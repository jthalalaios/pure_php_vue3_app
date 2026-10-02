import { defineStore } from 'pinia'
import { authService } from '../../services/auth.service'
import { User } from '../../models/User.model'
import type { LoginCredentials, RegisterDTO } from '../../models/Auth.model'

const AUTH_STORAGE_KEY = 'auth.signed_in'
const USER_STORAGE_KEY = 'auth.user'

function loadSavedUser(): User | null {
  try {
    const rawUser = localStorage.getItem(USER_STORAGE_KEY)
    if (rawUser) {
      return User.fromAPI(JSON.parse(rawUser))
    }
  } catch {
    return null
  }
  return null
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: loadSavedUser(),
    signedIn: localStorage.getItem(AUTH_STORAGE_KEY) === '1',
    isLoading: false,
    authError: null as string | null
  }),

  getters: {
    isAuthenticated: (state): boolean => state.signedIn && !!state.user,
    currentUser: (state): User => state.user || User.createEmpty(),
    userName: (state): string => state.user?.displayName || 'Guest',
    userInitials: (state): string => state.user?.initials || 'U'
  },

  actions: {
    setUser(user: User | null) {
      this.user = user
      if (user && user.id > 0) {
        this.signedIn = true
        localStorage.setItem(AUTH_STORAGE_KEY, '1')
        localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user.toJSON()))
      } else {
        this.signedIn = false
        localStorage.removeItem(AUTH_STORAGE_KEY)
        localStorage.removeItem(USER_STORAGE_KEY)
      }
    },

    /**
     * Initializes authentication state by verifying active session with the backend.
     */
    async initialize(): Promise<void> {
      try {
        const user = await authService.fetchCurrentUser()
        if (user) {
          this.setUser(user)
        } else {
          this.setUser(null)
        }
      } catch (err) {
        this.setUser(null)
      }
    },

    async login(credentials: LoginCredentials): Promise<{ user: User; redirect?: string }> {
      this.isLoading = true
      this.authError = null
      try {
        const result = await authService.login(credentials)
        this.setUser(result.user)
        return result
      } catch (err: any) {
        this.authError = err.message || 'Login failed'
        throw err
      } finally {
        this.isLoading = false
      }
    },

    async register(payload: RegisterDTO): Promise<{ message: string }> {
      this.isLoading = true
      this.authError = null
      try {
        const result = await authService.register(payload)
        return result
      } catch (err: any) {
        this.authError = err.message || 'Registration failed'
        throw err
      } finally {
        this.isLoading = false
      }
    },

    async logout(): Promise<void> {
      this.isLoading = true
      try {
        await authService.logout()
      } finally {
        this.setUser(null)
        this.isLoading = false
      }
    }
  }
})
