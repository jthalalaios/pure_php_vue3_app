import { TenantService, TENANT_REGISTRY } from './tenant.service'
import type { TenantId } from '../models/Tenant.model'

export type Theme = {
  name: string
  label: string
  cssVars: Record<string, string>
  bodyClass?: string
}

export const availableThemes: Theme[] = Object.values(TENANT_REGISTRY).map((t) => ({
  name: t.theme.name,
  label: t.theme.label,
  cssVars: t.theme.cssVars,
  bodyClass: t.theme.bodyClass
}))

export function applyTheme(themeName: string) {
  const tenantId = (themeName in TENANT_REGISTRY ? themeName : 'doctor') as TenantId
  const config = TenantService.getTenantConfig(tenantId)
  TenantService.applyTenantTheme(config)
  TenantService.persistTenant(tenantId)
}
