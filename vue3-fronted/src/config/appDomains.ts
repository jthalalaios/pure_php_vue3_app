import type { AppDomainConfig } from '../types/app'

export const appDomains: Record<string, AppDomainConfig> = {
  doctor: {
    id: 'doctor',
    label: 'Doctor App',
    description: 'Clinical operations and patient journey management',
    themeName: 'doctor',
    navItems: [
      { id: 1, title: 'Home', url: '/' },
      { id: 2, title: 'Appointments', url: '/appointments' },
      { id: 3, title: 'Patients', url: '/patients' },
      { id: 4, title: 'Reports', url: '/reports' }
    ],
    heroTitle: 'Manage visitors, patients, and appointments in one calm workspace.',
    heroSubtitle: 'A modern clinic experience with dynamic cards, live metrics, animated sections, and reusable domain-aware components.',
    ctaLabel: 'Open Dashboard',
    metrics: [
      { title: 'Visitors', value: '24', caption: 'Checked in today' },
      { title: 'Patients', value: '128', caption: 'Active records' },
      { title: 'Reports', value: '9', caption: 'Ready to review' }
    ],
    features: [
      { title: 'Appointments', body: 'Coordinate visits and follow-ups effortlessly.' },
      { title: 'Patients', body: 'Keep patient records and histories at your fingertips.' },
      { title: 'Reports', body: 'Track clinic performance with clear, shareable insights.' }
    ],
    sliderItems: [
      { id: 1, title: 'Morning rounds', subtitle: '09:00 · Consultation', accent: 'pi pi-calendar' },
      { id: 2, title: 'Follow-up care', subtitle: '09:30 · Review', accent: 'pi pi-heart' },
      { id: 3, title: 'New arrivals', subtitle: '10:00 · Admission', accent: 'pi pi-users' }
    ],
    dashboardRoute: '/dashboard'
  },
  marketplace: {
    id: 'marketplace',
    label: 'Marketplace App',
    description: 'Commerce, promotions, and customer engagement',
    themeName: 'marketplace',
    navItems: [
      { id: 1, title: 'Home', url: '/' },
      { id: 2, title: 'Products', url: '/products' },
      { id: 3, title: 'Orders', url: '/orders' },
      { id: 4, title: 'Marketing', url: '/marketing' }
    ],
    heroTitle: 'Launch products and grow revenue with a polished storefront experience.',
    heroSubtitle: 'A flexible marketplace UI with promo cards, commerce metrics, and animated conversion blocks.',
    ctaLabel: 'Explore Catalog',
    metrics: [
      { title: 'Revenue', value: '$12.4k', caption: 'This week' },
      { title: 'Orders', value: '318', caption: 'Live purchases' },
      { title: 'Customers', value: '2.1k', caption: 'Returning users' }
    ],
    features: [
      { title: 'Products', body: 'Showcase inventory with rich cards and promotions.' },
      { title: 'Orders', body: 'Track fulfillment and delivery status in real time.' },
      { title: 'Marketing', body: 'Run campaigns and measure customer engagement.' }
    ],
    sliderItems: [
      { id: 1, title: 'Hot deals', subtitle: 'Flash sale · 50% off', accent: 'pi pi-tags' },
      { id: 2, title: 'Best sellers', subtitle: 'Top rated products', accent: 'pi pi-star' },
      { id: 3, title: 'New arrivals', subtitle: 'Fresh inventory', accent: 'pi pi-shopping-cart' }
    ],
    dashboardRoute: '/market'
  }
}

export function getDefaultDomain(): AppDomainConfig {
  return appDomains.doctor
}

export function getDomainById(id: string): AppDomainConfig {
  return appDomains[id] || getDefaultDomain()
}

export function getDomainForTheme(themeName: string): AppDomainConfig {
  if (themeName === 'marketplace') return appDomains.marketplace
  return getDefaultDomain()
}
