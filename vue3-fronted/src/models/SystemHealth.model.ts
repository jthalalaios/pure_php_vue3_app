export interface PingResponse {
  status: 'ok' | 'error'
  timestamp: string
  session_id: string
  visits?: number
}

export interface DbTestResponse {
  status: 'ok' | 'error'
  version?: string
  message?: string
}

export interface RedisTestResponse {
  status: 'ok' | 'error'
  ping?: string | boolean
  message?: string
}

export interface DashboardApiResponse {
  status: 'ok' | 'error'
  session_id: string
  user: {
    id: number
    name: string
    email: string
    created_at: string
    is_verified: boolean
  }
  visits: number
  message?: string
}
