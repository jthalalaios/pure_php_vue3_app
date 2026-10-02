<script setup lang="ts">
import Navbar from './components/shared/navbar/Navbar.vue'
import Footer from './components/shared/Footer.vue'
import { splitTextAnimations } from './utils/InitAnimations'
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTenantStore } from './store/tenantStore'

const route = useRoute()
const tenantStore = useTenantStore()

onMounted(() => {
  splitTextAnimations()
  tenantStore.initializeTenant()
})
const isHome = computed(() => route.name === 'home')
</script>

<template>
  <Navbar :forceFixed="isHome" />
  <router-view />
  <Footer />
</template>
