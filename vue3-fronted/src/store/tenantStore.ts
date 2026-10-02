import { defineStore } from 'pinia'
import { TenantService } from '../services/tenant.service'
import type { TenantConfig, TenantId } from '../models/Tenant.model'

interface TenantState {
  currentTenantId: TenantId
  tenantConfig: TenantConfig
  isSwitching: boolean
}

export const useTenantStore = defineStore('tenant', {
  state: (): TenantState => {
    const initialTenantId = TenantService.resolveTenant()
    const config = TenantService.getTenantConfig(initialTenantId)

    return {
      currentTenantId: initialTenantId,
      tenantConfig: config,
      isSwitching: false
    }
  },

  getters: {
    activeTenant: (state): TenantConfig => state.tenantConfig,
    activeTheme: (state) => state.tenantConfig.theme,
    branding: (state) => state.tenantConfig.branding,
    tenantId: (state): TenantId => state.currentTenantId,
    tenantNavItems: (state) => state.tenantConfig.navItems,
    tenantFeatures: (state) => state.tenantConfig.features,
    allTenants: () => TenantService.getAllTenants()
  },

  actions: {
    /**
     * Initializes tenant and applies its theme variables globally
     */
    initializeTenant() {
      const resolved = TenantService.resolveTenant()
      this.switchTenant(resolved, false)
    },

    /**
     * Dynamically switches the active tenant and instantly propagates the theme
     */
    switchTenant(tenantId: TenantId, persist: boolean = true) {
      this.isSwitching = true
      const config = TenantService.getTenantConfig(tenantId)
      this.currentTenantId = tenantId
      this.tenantConfig = config

      if (persist) {
        TenantService.persistTenant(tenantId)
      }

      TenantService.applyTenantTheme(config)
      this.isSwitching = false
    }
  }
})
