<template>
  <section class="auth-shell">
    <div class="auth-card card">
      <div class="auth-card__header">
        <p class="eyebrow">{{ eyebrow }}</p>
        <h2>{{ title }}</h2>
        <p class="auth-card__subtitle">{{ subtitle }}</p>
      </div>
      <slot />
    </div>
  </section>
</template>

<script lang="ts" setup>
defineProps<{
  eyebrow: string
  title: string
  subtitle: string
}>()
</script>

<style scoped>
.auth-shell {
  min-height: calc(100vh - 120px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}
.auth-card {
  width: min(100%, 460px);
  padding: 2rem;
  background: linear-gradient(135deg, var(--surface) 0%, var(--surface-elevated) 100%);
  border: 1px solid var(--border);
  border-radius: 24px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}
.auth-card__header {
  margin-bottom: 1.25rem;
}
.eyebrow {
  color: var(--primary);
  font-size: 0.78rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  font-weight: 700;
  margin-bottom: 0.25rem;
}
.auth-card h2 {
  margin: 0 0 0.35rem;
  color: var(--text);
}
.auth-card__subtitle {
  color: var(--muted);
  line-height: 1.5;
}
@media (max-width: 640px) {
  .auth-card {
    padding: 1.25rem;
    border-radius: 18px;
  }
}
</style>
