<template>
  <div class="visitor-card">
    <div class="visitor-name">{{ visitor?.name }}</div>
    <div class="visitor-meta">{{ visitor?.time }} — {{ visitor?.reason }}</div>
    <div class="p-mt-2">
      <Button label="Details" icon="pi pi-search" class="p-button-text" />
      <Button label="Check-in" icon="pi pi-check" class="p-button-outlined p-ml-2" />
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { PropType } from 'vue'
import Button from 'primevue/button'

const props = defineProps({ visitor: { type: Object as PropType<Record<string, any>>, required: true } })
</script>

<style scoped>
@import '../doctor.css';
</style>
