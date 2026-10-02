<template>
  <div class="language-select" ref="containerRef">
    <button class="trigger" @click="toggleMenu" aria-label="Select language" title="Select language">
      <i class="pi pi-globe"></i>
    </button>

    <transition name="fade">
      <div v-if="open" class="menu">
        <button class="option" :class="{ active: locale === 'en' }" @click="selectLanguage('en')">
          <span class="flag" aria-hidden="true">🇬🇧</span>
          <span>English</span>
        </button>
        <button class="option" :class="{ active: locale === 'el' }" @click="selectLanguage('el')">
          <span class="flag" aria-hidden="true">🇬🇷</span>
          <span>Ελληνικά</span>
        </button>
      </div>
    </transition>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import i18n from '../../i18n'
import { loadLocaleMessages } from '../../utils/i18nLoader'

const { locale } = useI18n()
const open = ref(false)
const containerRef = ref<HTMLElement | null>(null)

function toggleMenu() {
  open.value = !open.value
}

function closeMenu() {
  open.value = false
}

async function selectLanguage(next: string) {
  const normalized = next === 'el' ? 'el' : 'en'
  locale.value = normalized
  localStorage.setItem('lang', normalized)

  if (globalThis.axios) {
    globalThis.axios.defaults.headers.common['Accept-Language'] = normalized
  }

  await loadLocaleMessages(i18n, normalized)
  closeMenu()
}

function onDocClick(e: MouseEvent) {
  if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
    closeMenu()
  }
}

onMounted(() => document.addEventListener('click', onDocClick))
onUnmounted(() => document.removeEventListener('click', onDocClick))
</script>

<style scoped>
.language-select {
  position: relative;
}

.trigger {
  color: var(--text);
  background: var(--surface-elevated);
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 0.45rem 0.6rem;
  cursor: pointer;
}

.menu {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 0.4rem;
  min-width: 140px;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
  z-index: 20;
}

.option {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  width: 100%;
  border: none;
  background: transparent;
  color: var(--text);
  text-align: left;
  padding: 0.5rem 0.6rem;
  border-radius: 8px;
  cursor: pointer;
}

.flag {
  font-size: 1rem;
  line-height: 1;
}

.option:hover,
.option.active {
  background: var(--surface-elevated);
  color: var(--primary);
}
</style>
