export type AppDomainId = 'doctor' | 'marketplace'

export interface NavItem {
  id: number
  title: string
  url: string
}

export interface MetricItem {
  title: string
  value: string
  caption: string
}

export interface FeatureItem {
  title: string
  body: string
}

export interface SliderItem {
  id: number
  title: string
  subtitle: string
  accent: string
}

export interface AppDomainConfig {
  id: AppDomainId
  label: string
  description: string
  themeName: string
  navItems: NavItem[]
  heroTitle: string
  heroSubtitle: string
  ctaLabel: string
  metrics: MetricItem[]
  features: FeatureItem[]
  sliderItems: SliderItem[]
  dashboardRoute: string
}
