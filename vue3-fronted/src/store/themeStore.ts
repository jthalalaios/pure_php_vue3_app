import { reactive } from 'vue'
import { TenantService } from '../services/tenant.service'
import { useTenantStore } from './tenantStore'

export function useThemeStore() {
  const tenantStore = useTenantStore()
  return {
    get currentThemeName() {
      return tenantStore.currentTenantId
    }
  }
}

export function initTheme() {
  const tenantStore = useTenantStore()
  tenantStore.initializeTenant()
}

export function setTheme(themeName: string) {
  const tenantStore = useTenantStore()
  tenantStore.switchTenant(themeName as any)
}
