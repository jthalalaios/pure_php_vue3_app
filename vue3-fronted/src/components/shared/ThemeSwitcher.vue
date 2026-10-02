<template>
  <div class="tenant-switcher-wrap">
    <label class="tenant-switcher" :title="activeTenant.tagline">
      <i :class="activeTenant.logoIcon" class="tenant-icon"></i>
      <span class="label">Tenant:</span>
      <select v-model="selectedTenant" aria-label="Tenant switcher">
        <option v-for="tenant in allTenants" :key="tenant.id" :value="tenant.id">
          {{ tenant.name }}
        </option>
      </select>
    </label>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useTenantStore } from '../../store/tenantStore'
import type { TenantId } from '../../models/Tenant.model'

const tenantStore = useTenantStore()

const allTenants = computed(() => tenantStore.allTenants)
const activeTenant = computed(() => tenantStore.activeTenant.branding)

const selectedTenant = computed({
  get: () => tenantStore.currentTenantId,
  set: (val: TenantId) => {
    tenantStore.switchTenant(val)
  }
})
</script>

<style scoped>
.tenant-switcher-wrap {
  display: inline-flex;
  align-items: center;
}

.tenant-switcher {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  background: var(--surface-elevated, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  color: var(--text, #1e293b);
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.tenant-switcher:hover {
  border-color: var(--primary, #0f66f0);
}

.tenant-icon {
  color: var(--primary, #0f66f0);
  font-size: 0.95rem;
}

.tenant-switcher .label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 700;
  color: var(--muted, #64748b);
}

.tenant-switcher select {
  background: transparent;
  color: var(--text, #1e293b);
  border: none;
  outline: none;
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
}

.tenant-switcher select option {
  background: var(--surface-elevated, #ffffff);
  color: var(--text, #1e293b);
}
</style>
