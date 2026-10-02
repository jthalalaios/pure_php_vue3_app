import type { TenantConfig, TenantId } from '../models/Tenant.model'

export const TENANT_REGISTRY: Record<TenantId, TenantConfig> = {
  doctor: {
    id: 'doctor',
    name: 'MediCare Clinical Portal',
    domain: 'clinic.local',
    subdomains: ['clinic', 'doctor', 'health'],
    branding: {
      appName: 'MediCare Clinic',
      logoIcon: 'pi pi-heart-fill',
      tagline: 'Healthcare & Clinical Management Workspace',
      primaryColor: '#0f66f0',
      accentColor: '#06b6d4'
    },
    theme: {
      id: 'doctor',
      name: 'doctor',
      label: 'Clinical Blue',
      bodyClass: 'theme-doctor',
      cssVars: {
        '--bg': '#f6fbff',
        '--text': '#0f1724',
        '--primary': '#0f66f0',
        '--primary-hover': '#0b52c1',
        '--surface': '#ffffff',
        '--surface-elevated': '#f8fbff',
        '--border': '#e2e8f0',
        '--accent': '#06b6d4',
        '--muted': '#64748b'
      }
    },
    navItems: [
      { id: 1, title: 'Home', url: '/', icon: 'pi pi-home' },
      { id: 2, title: 'Appointments', url: '/appointments', icon: 'pi pi-calendar' },
      { id: 3, title: 'Patients', url: '/patients', icon: 'pi pi-users' },
      { id: 4, title: 'Reports', url: '/reports', icon: 'pi pi-file' },
      { id: 5, title: 'Dashboard', url: '/dashboard', icon: 'pi pi-th-large' }
    ],
    features: {
      dashboardWidget: 'clinical',
      showQuickTests: true,
      analyticsEnabled: true,
      customModules: ['ehr', 'prescriptions']
    }
  },

  marketplace: {
    id: 'marketplace',
    name: 'OmniStore E-Commerce',
    domain: 'market.local',
    subdomains: ['market', 'shop', 'store'],
    branding: {
      appName: 'OmniStore Market',
      logoIcon: 'pi pi-shopping-bag',
      tagline: 'Multi-vendor Commerce & Merchandising Engine',
      primaryColor: '#f97316',
      accentColor: '#e11d48'
    },
    theme: {
      id: 'marketplace',
      name: 'marketplace',
      label: 'Marketplace Ember',
      bodyClass: 'theme-marketplace',
      cssVars: {
        '--bg': '#fffaf5',
        '--text': '#1c1917',
        '--primary': '#f97316',
        '--primary-hover': '#ea580c',
        '--surface': '#ffffff',
        '--surface-elevated': '#fff7ed',
        '--border': '#fed7aa',
        '--accent': '#e11d48',
        '--muted': '#78716c'
      }
    },
    navItems: [
      { id: 1, title: 'Home', url: '/', icon: 'pi pi-home' },
      { id: 2, title: 'Products', url: '/products', icon: 'pi pi-box' },
      { id: 3, title: 'Orders', url: '/orders', icon: 'pi pi-shopping-cart' },
      { id: 4, title: 'Marketing', url: '/marketing', icon: 'pi pi-megaphone' },
      { id: 5, title: 'Dashboard', url: '/dashboard', icon: 'pi pi-chart-bar' }
    ],
    features: {
      dashboardWidget: 'commerce',
      showQuickTests: true,
      analyticsEnabled: true,
      customModules: ['inventory', 'coupons']
    }
  },

  enterprise: {
    id: 'enterprise',
    name: 'Apex Enterprise Hub',
    domain: 'enterprise.local',
    subdomains: ['enterprise', 'corp', 'apex'],
    branding: {
      appName: 'Apex Enterprise',
      logoIcon: 'pi pi-building',
      tagline: 'Global Operations & Corporate Analytics',
      primaryColor: '#6366f1',
      accentColor: '#10b981'
    },
    theme: {
      id: 'enterprise',
      name: 'enterprise',
      label: 'Enterprise Slate',
      bodyClass: 'theme-enterprise',
      cssVars: {
        '--bg': '#0f172a',
        '--text': '#f8fafc',
        '--primary': '#6366f1',
        '--primary-hover': '#4f46e5',
        '--surface': '#1e293b',
        '--surface-elevated': '#334155',
        '--border': '#475569',
        '--accent': '#10b981',
        '--muted': '#94a3b8'
      }
    },
    navItems: [
      { id: 1, title: 'Home', url: '/', icon: 'pi pi-home' },
      { id: 2, title: 'Operations', url: '/operations', icon: 'pi pi-server' },
      { id: 3, title: 'Security', url: '/security', icon: 'pi pi-shield' },
      { id: 4, title: 'Audit Logs', url: '/audit', icon: 'pi pi-list' },
      { id: 5, title: 'Dashboard', url: '/dashboard', icon: 'pi pi-gauge' }
    ],
    features: {
      dashboardWidget: 'enterprise',
      showQuickTests: true,
      analyticsEnabled: true,
      customModules: ['compliance', 'roles']
    }
  }
}

export class TenantService {
  private static readonly STORAGE_KEY = 'app.current_tenant'

  /**
   * Resolves the active tenant using hierarchical resolution:
   * 1. URL search param (e.g. ?tenant=doctor)
   * 2. Hostname/subdomain resolution (e.g. clinic.example.com -> doctor)
   * 3. LocalStorage persistence
   * 4. Default fallback ('doctor')
   */
  public static resolveTenant(): TenantId {
    if (typeof window === 'undefined') return 'doctor'

    // 1. URL search params override (great for testing & deep links)
    const urlParams = new URLSearchParams(window.location.search)
    const queryTenant = urlParams.get('tenant')?.toLowerCase() as TenantId
    if (queryTenant && TENANT_REGISTRY[queryTenant]) {
      this.persistTenant(queryTenant)
      return queryTenant
    }

    // 2. Subdomain / Host detection
    const host = window.location.hostname.toLowerCase()
    for (const [id, config] of Object.entries(TENANT_REGISTRY)) {
      if (host === config.domain) return id as TenantId
      if (config.subdomains.some((sub) => host.startsWith(`${sub}.`))) {
        return id as TenantId
      }
    }

    // 3. LocalStorage saved preference
    const saved = localStorage.getItem(this.STORAGE_KEY) as TenantId
    if (saved && TENANT_REGISTRY[saved]) {
      return saved
    }

    // 4. Default
    return 'doctor'
  }

  public static getTenantConfig(id: TenantId): TenantConfig {
    return TENANT_REGISTRY[id] || TENANT_REGISTRY.doctor
  }

  public static getAllTenants(): TenantConfig[] {
    return Object.values(TENANT_REGISTRY)
  }

  public static persistTenant(id: TenantId): void {
    if (typeof window !== 'undefined') {
      localStorage.setItem(this.STORAGE_KEY, id)
    }
  }

  /**
   * Dynamically applies the CSS variables and classes of the tenant's theme
   */
  public static applyTenantTheme(config: TenantConfig): void {
    if (typeof document === 'undefined') return
    const root = document.documentElement
    const theme = config.theme

    // Inject CSS Custom Properties dynamically
    Object.entries(theme.cssVars).forEach(([property, value]) => {
      root.style.setProperty(property, value)
    })

    // Also update generic semantic theme variables
    root.style.setProperty('--tenant-primary', config.branding.primaryColor)
    root.style.setProperty('--tenant-accent', config.branding.accentColor)

    // Manage tenant class on document.body
    document.body.classList.remove(
      ...Object.values(TENANT_REGISTRY).map((t) => t.theme.bodyClass)
    )
    if (theme.bodyClass) {
      document.body.classList.add(theme.bodyClass)
    }

    // Update document title dynamically
    document.title = `${config.branding.appName}`

    // Update Axios request headers with current tenant context for backend APIs
    if (typeof globalThis !== 'undefined' && globalThis.axios) {
      globalThis.axios.defaults.headers.common['X-Tenant-ID'] = config.id
    }
  }
}
