export type TenantId = 'doctor' | 'marketplace' | 'enterprise'

export interface TenantBranding {
  appName: string
  logoIcon: string
  tagline: string
  primaryColor: string
  accentColor: string
}

export interface TenantThemeConfig {
  id: string
  name: string
  label: string
  bodyClass: string
  cssVars: Record<string, string>
}

export interface TenantConfig {
  id: TenantId
  name: string
  domain: string
  subdomains: string[]
  theme: TenantThemeConfig
  branding: TenantBranding
  navItems: {
    id: number
    title: string
    url: string
    icon?: string
  }[]
  features: {
    dashboardWidget: string
    showQuickTests: boolean
    analyticsEnabled: boolean
    customModules: string[]
  }
}
