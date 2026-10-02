import type { User } from './User.model'

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterDTO {
  name: string
  email: string
  password: string
  password_confirm?: string
}

export interface ForgotPasswordDTO {
  email: string
}

export interface ApiResponse<T = any> {
  status: 'ok' | 'error'
  message?: string
  errors?: Record<string, string>
  data?: T
}

export interface AuthApiResponse {
  status: 'ok' | 'error'
  message?: string
  redirect?: string
  errors?: Record<string, string>
  user?: {
    id: number
    name: string
    email: string
    is_verified?: boolean
    created_at?: string
  }
}

export interface CurrentUserApiResponse {
  status: 'ok' | 'error'
  authenticated: boolean
  session_id?: string
  user?: {
    id: number
    name: string
    email: string
    is_verified?: boolean
    created_at?: string
  }
}

export interface VerifyApiResponse {
  status: 'ok' | 'error'
  verified?: boolean
  message: string
}
