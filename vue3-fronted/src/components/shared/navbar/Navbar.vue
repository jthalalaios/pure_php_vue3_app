<script lang="ts" setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import LanguageSelect from '../LanguageSelect.vue'
import TimezoneSelector from '../TimezoneSelector.vue'
import Notification from '../Notification.vue'
import Profile from '../Profile.vue'
import ThemeSwitcher from '../ThemeSwitcher.vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../../store/Users/authStore'
import { useTenantStore } from '../../../store/tenantStore'

const props = defineProps({ forceFixed: { type: Boolean, default: false } })
const route = useRoute()
const authStore = useAuthStore()
const tenantStore = useTenantStore()
const { t } = useI18n()
const isLoggedIn = computed(() => authStore.isAuthenticated)
const headerStyle = computed(() => ({
  '--header-accent': tenantStore.branding.primaryColor || '#0f66f0'
}))
const navOpen = ref(false)
const scrolled = ref(false)

const menuData = computed(() => {
  return tenantStore.tenantNavItems.map((item) => ({
    ...item,
    title: t(`nav.${item.title.toLowerCase()}`) || item.title
  }))
})
const brandLabel = computed(() => tenantStore.branding.appName)
const brandIcon = computed(() => tenantStore.branding.logoIcon)

const timezoneItems = [Intl.DateTimeFormat().resolvedOptions().timeZone]
function handleTimezoneSelect(t: string) { localStorage.setItem('timezone', t) }

const authOpen = ref(false)
const authRef = ref<HTMLElement | null>(null)
const accountOpen = ref(false)
const accountRef = ref<HTMLElement | null>(null)

function toggleAuth() { authOpen.value = !authOpen.value }
function closeAuth() { authOpen.value = false }
function toggleAccountMenu() { accountOpen.value = !accountOpen.value }
function closeAccountMenu() { accountOpen.value = false }
function handleLogout() {
  authStore.logout()
  closeAccountMenu()
}

function onDocClick(e: MouseEvent) {
  if (!authRef.value) return
  if (!authRef.value.contains(e.target as Node)) authOpen.value = false
  if (accountRef.value && !accountRef.value.contains(e.target as Node)) accountOpen.value = false
}

function toggleSub(id: number) {
  // placeholder
}

function onScroll() { scrolled.value = window.scrollY > 60 }
onMounted(() => { window.addEventListener('scroll', onScroll); document.addEventListener('click', onDocClick) })
onUnmounted(() => { window.removeEventListener('scroll', onScroll); document.removeEventListener('click', onDocClick) })
</script>

<template>
  <header :class="['header-section', { 'fixed-header': scrolled || forceFixed }]" :style="headerStyle">
    <div class="container-inner">
      <div class="brand-area">
        <RouterLink to="/" class="brand">
          <i :class="brandIcon" class="me-2"></i>
          {{ brandLabel }}
        </RouterLink>
      </div>

      <button class="burger" @click="navOpen = !navOpen" aria-label="menu">
        <span></span><span></span><span></span>
      </button>

      <nav class="nav" :class="{ open: navOpen }">
        <ul class="nav-list">
          <li v-for="item in menuData" :key="item.id" class="nav-item">
            <RouterLink v-if="item.url" :to="item.url">{{ item.title }}</RouterLink>
            <div v-else class="has-sub">
              <button @click="toggleSub(item.id)">{{ item.title }}</button>
              <ul v-if="item.submenus" class="sub">
                <li v-for="s in item.submenus" :key="s.id"><RouterLink :to="s.url">{{ s.title }}</RouterLink></li>
              </ul>
            </div>
          </li>
        </ul>
      </nav>

      <div class="actions">
        <ThemeSwitcher />

        <LanguageSelect />

        <div v-if="isLoggedIn" class="account-menu" ref="accountRef">
          <TimezoneSelector :items="timezoneItems" :onSelect="handleTimezoneSelect" />
          <button class="icon-btn" @click="toggleAccountMenu" aria-label="Account menu" title="Account menu">
            <i class="pi pi-user"></i>
          </button>

          <transition name="fade">
            <div v-if="accountOpen" class="account-dropdown" @click.stop>
              <ul>
                <li class="dropdown-header">
                  <span class="user-name-label">{{ authStore.userName }}</span>
                </li>
                <li><RouterLink to="/dashboard" @click="closeAccountMenu"><i class="pi pi-th-large me-2"></i>{{ t('nav.dashboard') || 'Dashboard' }}</RouterLink></li>
                <li><button class="dropdown-action" @click="handleLogout"><i class="pi pi-sign-out me-2"></i>{{ t('common.logout') }}</button></li>
              </ul>
            </div>
          </transition>
        </div>

        <div v-if="!isLoggedIn" class="auth-person" ref="authRef">
          <button class="person-btn" @click="toggleAuth()" aria-haspopup="true" :aria-expanded="authOpen">
            <i class="pi pi-user" style="font-size:1.15rem"></i>
          </button>

          <transition name="fade">
            <div v-if="authOpen" class="auth-popover" @click.stop>
              <ul>
                <li><RouterLink to="/register" @click="closeAuth">{{ t('common.signUp') }}</RouterLink></li>
                <li><RouterLink to="/signin" @click="closeAuth">{{ t('common.signIn') }}</RouterLink></li>
              </ul>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>


<style scoped>
.header-section {
  position: sticky;
  top: 0;
  z-index: 60;
  background: linear-gradient(90deg, var(--surface) 0%, var(--surface-elevated) 100%);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
  transition: background 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
}
.container-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  gap: 1rem;
}
.brand-area {
  min-width: 120px;
}
.brand {
  font-weight: 700;
  color: var(--primary);
  letter-spacing: 0.02em;
  text-decoration: none;
}
.burger {
  display: none;
  background: none;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0.4rem;
  color: var(--text);
}
.burger span {
  display: block;
  width: 18px;
  height: 2px;
  background: currentColor;
  margin: 3px 0;
}
.nav {
  display: flex;
}
.nav-list {
  display: flex;
  gap: 1rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
.nav-list a,
.nav-list button {
  color: var(--text);
  text-decoration: none;
  background: none;
  border: none;
  cursor: pointer;
  font: inherit;
  transition: color 180ms ease;
}
.nav-list a:hover,
.nav-list button:hover {
  color: var(--primary);
}
.actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
  justify-content: flex-end;
}
.account-menu {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}
.icon-btn,
.person-btn {
  color: var(--text);
  background: var(--surface-elevated);
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 0.45rem 0.6rem;
  cursor: pointer;
}
@media (max-width: 768px) {
  .container-inner {
    align-items: flex-start;
  }
  .burger { display: block; margin-left: auto; }
  .nav {
    display: none;
    width: 100%;
    order: 3;
  }
  .nav.open {
    display: block;
    background: var(--surface);
    padding: 0.85rem 0 0.25rem;
    border-top: 1px solid var(--border);
    margin-top: 0.25rem;
  }
  .nav-list {
    flex-direction: column;
    gap: 0.7rem;
  }
  .actions {
    width: 100%;
    justify-content: flex-start;
    margin-top: 0.25rem;
  }
}

.auth-person,
.account-menu {
  position: relative;
}
.auth-popover,
.account-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 0.5rem;
  min-width: 160px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
}
.auth-popover ul,
.account-dropdown ul { list-style: none; margin: 0; padding: 0; }
.auth-popover li,
.account-dropdown li { padding: 0.25rem 0; }
.auth-popover li a,
.account-dropdown li a,
.dropdown-action {
  color: var(--text);
  text-decoration: none;
  display: block;
  padding: 0.4rem 0.5rem;
  border-radius: 8px;
  width: 100%;
  text-align: left;
  background: transparent;
  border: none;
  cursor: pointer;
  font: inherit;
}
.auth-popover li a:hover,
.account-dropdown li a:hover,
.dropdown-action:hover {
  background: var(--surface-elevated);
  color: var(--primary);
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
