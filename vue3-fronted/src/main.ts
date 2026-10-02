import { createApp } from 'vue'
import App from './App.vue'
import './styles.css'
import { createPinia } from 'pinia'
import router from './router'
import i18n from './i18n'
import { loadLocaleMessages } from './utils/i18nLoader'
import axios from 'axios'
import { useAuthStore } from './store/Users/authStore'
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import 'primeicons/primeicons.css'
import 'primevue/resources/primevue.min.css'
import './themes/theme-base.css'
import { applyTheme, availableThemes } from './services/theme'
import './themes/doctor.css'

const pinia = createPinia()
const app = createApp(App)
app.use(pinia)
app.use(router)
app.use(i18n)
app.use(PrimeVue, { ripple: true })
app.use(ToastService)

// Determine locale
const rawLocale = localStorage.getItem('lang') || navigator.language?.split('-')[0] || 'en'
const locale = rawLocale.toLowerCase() === 'gr' ? 'el' : rawLocale.split('-')[0]

// Determine timezone (try stored setting else fallback to system)
let rawTimezone = localStorage.getItem('timezone') || Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'

type AxiosInstance = typeof axios;
declare global {
	var axios: AxiosInstance;
}
globalThis.axios = axios;

// Axios global settings
globalThis.axios = axios
globalThis.axios.defaults.headers.common['Content-Type'] = 'application/json;charset=utf-8'
globalThis.axios.defaults.headers.common['Accept'] = 'application/json'
globalThis.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
globalThis.axios.defaults.headers.common['Accept-Language'] = locale
globalThis.axios.defaults.withCredentials = true

const rawBaseApi = (import.meta.env.VITE_API_URL || 'http://localhost:8080')
	.replace(/\/api\/?$/, '')
	.replace(/\/+$/, '')
globalThis.axios.defaults.baseURL = rawBaseApi

// Request/response interceptors
const authStore = useAuthStore()
globalThis.axios.interceptors.request.use((config: any) => config, (error: any) => Promise.reject(error))

globalThis.axios.interceptors.response.use(
	(response: any) => response,
	(error: any) => {
		if (error?.response?.status === 401) {
			try { router.push('/signin') } catch (e) { router.push('/') }
		}
		return Promise.reject(error)
	}
)

import { useTenantStore } from './store/tenantStore'

// Load locale messages and initialize session then mount
const tenantStore = useTenantStore()
tenantStore.initializeTenant()

Promise.all([
	loadLocaleMessages(i18n, locale),
	authStore.initialize()
]).then(() => {
	localStorage.setItem('lang', locale)
	// persist detected timezone for later loads
	localStorage.setItem('timezone', rawTimezone)
	// expose timezone to backend via header
	globalThis.axios.defaults.headers.common['X-Timezone'] = rawTimezone
	app.mount('#app')
})
