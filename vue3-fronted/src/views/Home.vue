<script lang="ts" setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Button from 'primevue/button'
import Carousel from 'primevue/carousel'
import VisitorCard from '../themes/doctor/VisitorCard.vue'
import { getDomainForTheme } from '../config/appDomains'
import { useTenantStore } from '../store/tenantStore'

const router = useRouter()
const tenantStore = useTenantStore()
const { t } = useI18n()
const domain = computed(() => getDomainForTheme(tenantStore.currentTenantId))
const content = computed(() => {
  const themeKey = tenantStore.currentTenantId === 'marketplace'
    ? 'marketplace'
    : (tenantStore.currentTenantId === 'enterprise' ? 'marketplace' : 'doctor')
  return {
    experience: tenantStore.branding.tagline,
    heroTitle: tenantStore.activeTenant.name,
    heroSubtitle: t(`home.${themeKey}.heroSubtitle`),
    ctaLabel: t(`home.${themeKey}.ctaLabel`),
    metrics: [
      {
        title: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'visitors' : 'revenue'}.title`),
        value: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'visitors' : 'revenue'}.value`),
        caption: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'visitors' : 'revenue'}.caption`)
      },
      {
        title: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'patients' : 'orders'}.title`),
        value: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'patients' : 'orders'}.value`),
        caption: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'patients' : 'orders'}.caption`)
      },
      {
        title: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'reports' : 'customers'}.title`),
        value: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'reports' : 'customers'}.value`),
        caption: t(`home.${themeKey}.metrics.${themeKey === 'doctor' ? 'reports' : 'customers'}.caption`)
      }
    ],
    features: themeKey === 'doctor'
      ? [
          { title: t('home.doctor.features.appointments.title'), body: t('home.doctor.features.appointments.body') },
          { title: t('home.doctor.features.patients.title'), body: t('home.doctor.features.patients.body') },
          { title: t('home.doctor.features.reports.title'), body: t('home.doctor.features.reports.body') }
        ]
      : [
          { title: t('home.marketplace.features.products.title'), body: t('home.marketplace.features.products.body') },
          { title: t('home.marketplace.features.orders.title'), body: t('home.marketplace.features.orders.body') },
          { title: t('home.marketplace.features.marketing.title'), body: t('home.marketplace.features.marketing.body') }
        ]
  }
})

const visitors = ref([
  { id: 1, name: 'John Doe', time: '09:00', reason: 'Consultation' },
  { id: 2, name: 'Jane Smith', time: '09:30', reason: 'Follow-up' },
  { id: 3, name: 'Peter Parker', time: '10:00', reason: 'New patient' },
  { id: 4, name: 'Mary Major', time: '10:30', reason: 'Exam' }
])

const numVisible = ref(1)
function updateVisible() {
  if (window.innerWidth >= 1024) numVisible.value = 3
  else if (window.innerWidth >= 640) numVisible.value = 2
  else numVisible.value = 1
}

onMounted(() => {
  updateVisible()
  window.addEventListener('resize', updateVisible)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateVisible)
})

function goDashboard() { router.push(domain.value.dashboardRoute) }
</script>

<template>
  <div class="home-root">
    <main class="main p-4">
      <section class="hero card fade-in">
        <div class="hero-left">
          <p class="eyebrow">{{ content.experience }}</p>
          <h1 class="hero-title">{{ content.heroTitle }}</h1>
          <p class="hero-sub">{{ content.heroSubtitle }}</p>
          <div class="cta">
            <Button :label="content.ctaLabel" icon="pi pi-chart-line" @click="goDashboard" />
          </div>
        </div>
        <div class="hero-right">
          <Carousel :value="visitors" :numVisible="numVisible" :numScroll="1" class="visitor-carousel">
            <template #item="{ item }">
              <VisitorCard :visitor="item" />
            </template>
          </Carousel>
        </div>
      </section>

      <section class="stats-grid fade-up">
        <div v-for="metric in content.metrics" :key="metric.title" class="stat-card card">
          <div class="stat-title">{{ metric.title }}</div>
          <div class="stat-value">{{ metric.value }}</div>
          <div class="stat-caption">{{ metric.caption }}</div>
        </div>
      </section>

      <section class="features fade-up">
        <div v-for="feature in content.features" :key="feature.title" class="feature card">
          <h3>{{ feature.title }}</h3>
          <p>{{ feature.body }}</p>
        </div>
      </section>
    </main>
  </div>
</template>


<style scoped>
.nav { display:flex; justify-content:space-between; align-items:center; padding: .5rem 1rem; background: rgba(0,0,0,0.05); position:sticky; top:0; z-index:50 }
.nav-left { display:flex; align-items:center; gap:1rem }
.brand { font-weight:700; font-size:1.1rem }
.nav-links a { margin-right: .75rem; color: var(--text); text-decoration: none }
.nav-right { display:flex; align-items:center; gap:.5rem }
.icon-btn { background: transparent; border: none }
.avatar { cursor: pointer }

.main { max-width:1200px; margin:0 auto }
.hero { display:flex; gap:1.5rem; align-items:center; padding:1.4rem; background: linear-gradient(135deg, var(--surface) 0%, var(--surface-elevated) 100%); }
.hero-left { flex:1 }
.hero-right { flex:1 }
.eyebrow { text-transform: uppercase; letter-spacing: .2em; font-size: .78rem; color: var(--primary); font-weight: 700; margin-bottom: .5rem }
.hero-title { font-size: clamp(1.7rem, 3vw, 2.4rem); margin:0 0 .6rem; color: var(--text) }
.hero-sub { color: var(--muted); line-height: 1.6 }
.cta { margin-top: 1rem }

.stats-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:1rem; margin-top:1.25rem }
.stat-card { padding:1rem }
.stat-title { color: var(--muted); font-size: .85rem; text-transform: uppercase; letter-spacing: .08em }
.stat-value { font-size: 1.8rem; font-weight: 700; margin: .35rem 0 }
.stat-caption { color: var(--muted) }

.features { display:grid; grid-template-columns: repeat(3,1fr); gap:1rem; margin-top:1.25rem }
.feature { padding:1rem }

/* Animations */
.fade-in { animation: fadeIn .6s ease both }
.fade-up { animation: slideUp .6s ease both }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
@keyframes slideUp { from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:none} }

/* Responsive adjustments */
@media (max-width: 767px) {
  .features { grid-template-columns: 1fr }
  .hero { flex-direction: column }
}
</style>
