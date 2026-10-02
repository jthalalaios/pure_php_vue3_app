/**
 * Unified auth service interface and legacy compatibility wrappers
 */
export * from './auth.service'
export { default } from './auth.service'

import { authService } from './auth.service'
import type { LoginPayload, RegisterPayload, ForgotPayload, AuthResponse } from '../types/auth'

export async function login(payload: LoginPayload): Promise<AuthResponse> {
  const result = await authService.login(payload)
  return {
    token: '',
    user: result.user.toJSON()
  }
}

export async function register(payload: RegisterPayload): Promise<any> {
  return await authService.register(payload)
}

export async function forgotPassword(payload: ForgotPayload): Promise<any> {
  return await authService.forgotPassword(payload)
}
