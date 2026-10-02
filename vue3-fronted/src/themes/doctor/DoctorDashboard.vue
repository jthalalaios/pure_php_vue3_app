<template>
  <div>
    <div class="doctor-header">
      <div>
        <p class="visitor-meta">Today's visitors overview</p>
      </div>
      <div>
        <Button label="New Visit" icon="pi pi-plus" />
      </div>
    </div>

    <div class="visitor-grid">
      <VisitorCard v-for="v in visitors" :key="v.id" :visitor="v" />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import VisitorCard from './VisitorCard.vue'
import Button from 'primevue/button'

const visitors = ref([
  { id: 1, name: 'John Doe', time: '09:00', reason: 'Consultation' },
  { id: 2, name: 'Jane Smith', time: '09:30', reason: 'Follow-up' },
  { id: 3, name: 'Peter Parker', time: '10:00', reason: 'New patient' }
])
</script>

<style scoped>
@import '../doctor.css';
</style>
