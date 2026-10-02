<template>
  <select v-model="tz" @change="onSelect">
    <option v-for="t in items" :key="t" :value="t">{{ t }}</option>
  </select>
</template>

<script lang="ts" setup>
import { ref, toRef } from 'vue'
const props = defineProps({ items: { type: Array as any, default: () => Intl.DateTimeFormat().resolvedOptions().timeZone ? [Intl.DateTimeFormat().resolvedOptions().timeZone] : ['UTC'] }, onSelect: Function })
const tz = ref((props.items && props.items[0]) || 'UTC')
function onSelect() { props.onSelect?.(tz.value) }
</script>

<style scoped>
select { padding:4px }
</style>
