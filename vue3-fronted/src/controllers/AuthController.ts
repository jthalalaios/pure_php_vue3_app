import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/Users/authStore'
import type { LoginCredentials, RegisterDTO } from '../models/Auth.model'

export interface PasswordStrengthLevel {
  score: number
  percent: number
  colorClass: string
  label: string
}

export function useAuthController() {
  const router = useRouter()
  const authStore = useAuthStore()

  const isSubmitting = ref(false)
  const generalError = ref<string | null>(null)
  const successMessage = ref<string | null>(null)
  const fieldErrors = reactive<Record<string, string>>({})

  // Form states
  const loginForm = reactive<LoginCredentials>({
    email: '',
    password: ''
  })

  const registerForm = reactive<RegisterDTO>({
    name: '',
    email: '',
    password: '',
    password_confirm: ''
  })

  const showPassword = ref(false)

  function togglePasswordVisibility() {
    showPassword.value = !showPassword.value
  }

  function clearErrors() {
    generalError.value = null
    successMessage.value = null
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
  }

  /**
   * Password strength scoring algorithm ported directly from app.js
   * Returns a score between 0 and 4.
   */
  function calculatePasswordStrength(password: string): number {
    if (!password || password.length < 4) return 0
    let score = 0
    if (password.length >= 8) score++
    if (password.length >= 12) score++
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++
    if (/[0-9]/.test(password)) score++
    if (/[^A-Za-z0-9]/.test(password)) score++
    return Math.min(4, score)
  }

  const passwordStrength = computed<PasswordStrengthLevel>(() => {
    const score = calculatePasswordStrength(registerForm.password)
    const levels: Record<number, { percent: number; colorClass: string; label: string }> = {
      0: { percent: 0, colorClass: '', label: '' },
      1: { percent: 25, colorClass: 'bg-danger', label: 'Weak' },
      2: { percent: 50, colorClass: 'bg-warning', label: 'Fair' },
      3: { percent: 75, colorClass: 'bg-info', label: 'Good' },
      4: { percent: 100, colorClass: 'bg-success', label: 'Strong' }
    }
    const current = levels[score] || levels[0]
    return {
      score,
      percent: current.percent,
      colorClass: current.colorClass,
      label: current.label
    }
  })

  const isPasswordMatch = computed<boolean>(() => {
    if (!registerForm.password_confirm) return true
    return registerForm.password === registerForm.password_confirm
  })

  /**
   * Performs login with validation and error handling
   */
  async function submitLogin(onSuccess?: () => void) {
    clearErrors()

    if (!loginForm.email) {
      fieldErrors.email = 'Email is required.'
    }
    if (!loginForm.password) {
      fieldErrors.password = 'Password is required.'
    }
    if (Object.keys(fieldErrors).length > 0) return

    isSubmitting.value = true
    try {
      const res = await authStore.login(loginForm)
      if (onSuccess) {
        onSuccess()
      } else {
        router.push(res.redirect || '/dashboard')
      }
    } catch (err: any) {
      if (err.fieldErrors) {
        Object.assign(fieldErrors, err.fieldErrors)
      } else {
        generalError.value = err.message || 'Login failed.'
      }
    } finally {
      isSubmitting.value = false
    }
  }

  /**
   * Performs registration with validation
   */
  async function submitRegister(onSuccess?: () => void) {
    clearErrors()

    if (!registerForm.name || registerForm.name.length < 2) {
      fieldErrors.name = 'Full Name must be at least 2 characters.'
    }
    if (!registerForm.email) {
      fieldErrors.email = 'Enter a valid email address.'
    }
    if (!registerForm.password || registerForm.password.length < 8) {
      fieldErrors.password = 'Password must be at least 8 characters.'
    }
    if (registerForm.password !== registerForm.password_confirm) {
      fieldErrors.password_confirm = 'Passwords do not match.'
    }

    if (Object.keys(fieldErrors).length > 0) return

    isSubmitting.value = true
    try {
      const res = await authStore.register(registerForm)
      successMessage.value = res.message || 'Registration successful! Check your email.'
      // Reset registration form
      registerForm.name = ''
      registerForm.email = ''
      registerForm.password = ''
      registerForm.password_confirm = ''

      if (onSuccess) {
        onSuccess()
      } else {
        setTimeout(() => {
          router.push('/signin')
        }, 1500)
      }
    } catch (err: any) {
      if (err.fieldErrors) {
        Object.assign(fieldErrors, err.fieldErrors)
      } else {
        generalError.value = err.message || 'Registration failed.'
      }
    } finally {
      isSubmitting.value = false
    }
  }

  async function submitLogout() {
    isSubmitting.value = true
    try {
      await authStore.logout()
      router.push('/signin')
    } finally {
      isSubmitting.value = false
    }
  }

  return {
    loginForm,
    registerForm,
    showPassword,
    togglePasswordVisibility,
    passwordStrength,
    isPasswordMatch,
    isSubmitting,
    generalError,
    successMessage,
    fieldErrors,
    clearErrors,
    submitLogin,
    submitRegister,
    submitLogout
  }
}
