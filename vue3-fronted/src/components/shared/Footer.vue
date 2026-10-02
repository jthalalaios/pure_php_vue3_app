<template>
  <footer class="app-footer">
    <div class="content">
      <span>{{ t('footer.copyright', { year, brand: brandName, rightsReserved: t('common.allRightsReserved') }) }}</span>
    </div>
  </footer>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useThemeStore } from '../../store/themeStore'

const { t } = useI18n()
const themeStore = useThemeStore()
const year = new Date().getFullYear()
const brandName = computed(() => themeStore.currentThemeName === 'marketplace' ? t('common.marketplace') : t('common.clinic'))
</script>

<style scoped>
.app-footer {
  text-align: center;
  color: var(--muted);
  background: transparent;
  padding: 1.25rem 1rem 2rem;
}
.content {
  border-top: 1px solid var(--border);
  padding-top: 1rem;
}
</style>
