import { ref, onMounted } from 'vue'
import { systemService } from '../services/system.service'
import { useAuthStore } from '../store/Users/authStore'
import { User } from '../models/User.model'

export function useDashboardController() {
  const authStore = useAuthStore()

  const user = ref<User>(authStore.currentUser)
  const sessionId = ref<string>('')
  const visits = ref<number>(1)
  const isLoading = ref<boolean>(false)

  // Quick tests state
  const isTestingDb = ref<boolean>(false)
  const isTestingRedis = ref<boolean>(false)
  const isPinging = ref<boolean>(false)
  const testResult = ref<string | null>(null)
  const testStatus = ref<'idle' | 'success' | 'error'>('idle')

  async function loadDashboardData() {
    isLoading.value = true
    try {
      const data = await systemService.fetchDashboard()
      if (data.status === 'ok') {
        sessionId.value = data.session_id
        visits.value = data.visits
        if (data.user) {
          user.value = User.fromAPI(data.user)
          authStore.setUser(user.value)
        }
      }
    } catch (err) {
      // Fallback to authStore user
      user.value = authStore.currentUser
    } finally {
      isLoading.value = false
    }
  }

  async function runDbTest() {
    isTestingDb.value = true
    testResult.value = 'Connecting to PostgreSQL...'
    try {
      const res = await systemService.testDb()
      testStatus.value = res.status === 'ok' ? 'success' : 'error'
      testResult.value = JSON.stringify(res, null, 2)
    } catch (err: any) {
      testStatus.value = 'error'
      testResult.value = JSON.stringify({ status: 'error', message: err.message || 'Database test failed' }, null, 2)
    } finally {
      isTestingDb.value = false
    }
  }

  async function runRedisTest() {
    isTestingRedis.value = true
    testResult.value = 'Pinging Redis instance...'
    try {
      const res = await systemService.testRedis()
      testStatus.value = res.status === 'ok' ? 'success' : 'error'
      testResult.value = JSON.stringify(res, null, 2)
    } catch (err: any) {
      testStatus.value = 'error'
      testResult.value = JSON.stringify({ status: 'error', message: err.message || 'Redis test failed' }, null, 2)
    } finally {
      isTestingRedis.value = false
    }
  }

  async function runPing() {
    isPinging.value = true
    testResult.value = 'Pinging API...'
    try {
      const res = await systemService.ping()
      testStatus.value = res.status === 'ok' ? 'success' : 'error'
      testResult.value = JSON.stringify(res, null, 2)
      if (res.session_id) sessionId.value = res.session_id
      if (res.visits) visits.value = res.visits
    } catch (err: any) {
      testStatus.value = 'error'
      testResult.value = JSON.stringify({ status: 'error', message: err.message || 'Ping failed' }, null, 2)
    } finally {
      isPinging.value = false
    }
  }

  onMounted(() => {
    loadDashboardData()
  })

  return {
    user,
    sessionId,
    visits,
    isLoading,
    isTestingDb,
    isTestingRedis,
    isPinging,
    testResult,
    testStatus,
    loadDashboardData,
    runDbTest,
    runRedisTest,
    runPing
  }
}
