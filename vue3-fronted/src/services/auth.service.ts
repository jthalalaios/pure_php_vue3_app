import axios, { AxiosError } from 'axios'
import { User } from '../models/User.model'
import type {
  LoginCredentials,
  RegisterDTO,
  ForgotPasswordDTO,
  AuthApiResponse,
  CurrentUserApiResponse,
  VerifyApiResponse
} from '../models/Auth.model'

/**
 * Common Authentication Service
 * Handles all network calls for Login, Register, Logout, Current User session,
 * and Email Verification.
 */
export class AuthService {
  private readonly client = globalThis.axios || axios

  /**
   * Performs user login
   */
  public async login(credentials: LoginCredentials): Promise<{ user: User; redirect?: string }> {
    try {
      const response = await this.client.post<AuthApiResponse>('/api/login.php', {
        email: credentials.email,
        password: credentials.password
      })

      const data = response.data
      if (data.status !== 'ok') {
        throw new Error(data.message || 'Login failed')
      }

      const user = data.user ? User.fromAPI(data.user) : User.createEmpty()
      return { user, redirect: data.redirect || '/dashboard' }
    } catch (error: any) {
      throw this.normalizeError(error)
    }
  }

  /**
   * Registers a new user account
   */
  public async register(payload: RegisterDTO): Promise<{ message: string }> {
    try {
      const response = await this.client.post<AuthApiResponse>('/api/register.php', {
        name: payload.name,
        email: payload.email,
        password: payload.password,
        password_confirm: payload.password_confirm || payload.password
      })

      const data = response.data
      if (data.status !== 'ok') {
        throw new Error(data.message || 'Registration failed')
      }

      return {
        message: data.message || 'Registration successful! Please check your email to verify your account.'
      }
    } catch (error: any) {
      throw this.normalizeError(error)
    }
  }

  /**
   * Destroys current session on backend and frontend
   */
  public async logout(): Promise<void> {
    try {
      await this.client.post('/api/logout.php', {}, {
        headers: { Accept: 'application/json' }
      })
    } catch (error) {
      // In case POST is blocked or redirects, fallback to GET
      try {
        await this.client.get('/api/logout.php', {
          headers: { Accept: 'application/json' }
        })
      } catch (ignored) {
        // Continue local logout even if network has failed
      }
    }
  }

  /**
   * Fetches currently authenticated user and session status from `/api/me.php`
   */
  public async fetchCurrentUser(): Promise<User | null> {
    try {
      const response = await this.client.get<CurrentUserApiResponse>('/api/me.php')
      if (response.data.status === 'ok' && response.data.authenticated && response.data.user) {
        return User.fromAPI(response.data.user)
      }
      return null
    } catch (error) {
      return null
    }
  }

  /**
   * Verifies account token from verification link
   */
  public async verifyEmail(token: string): Promise<{ verified: boolean; message: string }> {
    try {
      const response = await this.client.get<VerifyApiResponse>(`/api/verify.php?token=${encodeURIComponent(token)}`)
      return {
        verified: Boolean(response.data.verified),
        message: response.data.message
      }
    } catch (error: any) {
      const normalized = this.normalizeError(error)
      return {
        verified: false,
        message: normalized.message || 'Verification failed or expired.'
      }
    }
  }

  /**
   * Forgot password request placeholder
   */
  public async forgotPassword(payload: ForgotPasswordDTO): Promise<{ message: string }> {
    try {
      const response = await this.client.post<AuthApiResponse>('/api/auth/forgot', payload)
      return {
        message: response.data.message || 'If that email exists, password reset instructions were sent.'
      }
    } catch (error: any) {
      // Fallback response for friendly UX
      return {
        message: 'If that email exists, password reset instructions were sent.'
      }
    }
  }

  /**
   * Normalizes backend Axios / PHP error responses
   */
  private normalizeError(error: any): Error & { fieldErrors?: Record<string, string> } {
    if (axios.isAxiosError(error) && error.response) {
      const data = error.response.data as any
      const message = data?.message || error.message || 'An unexpected error occurred.'
      const err = new Error(message) as Error & { fieldErrors?: Record<string, string> }
      if (data?.errors && typeof data.errors === 'object') {
        err.fieldErrors = data.errors
      }
      return err
    }
    return error instanceof Error ? error : new Error(String(error))
  }
}

export const authService = new AuthService()
export default authService
