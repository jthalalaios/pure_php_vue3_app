import axios from 'axios'
import type {
  PingResponse,
  DbTestResponse,
  RedisTestResponse,
  DashboardApiResponse
} from '../models/SystemHealth.model'

export class SystemService {
  private readonly client = globalThis.axios || axios

  public async ping(): Promise<PingResponse> {
    const res = await this.client.get<PingResponse>('/api/health.php?action=ping')
    return res.data
  }

  public async testDb(): Promise<DbTestResponse> {
    const res = await this.client.get<DbTestResponse>('/api/health.php?action=db_test')
    return res.data
  }

  public async testRedis(): Promise<RedisTestResponse> {
    const res = await this.client.get<RedisTestResponse>('/api/health.php?action=redis_test')
    return res.data
  }

  public async fetchDashboard(): Promise<DashboardApiResponse> {
    const res = await this.client.get<DashboardApiResponse>('/api/dashboard.php')
    return res.data
  }
}

export const systemService = new SystemService()
export default systemService
