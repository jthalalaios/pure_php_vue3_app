<template>
  <AuthCard :eyebrow="t('auth.resetAccess')" :title="t('auth.forgotPasswordTitle')" :subtitle="t('auth.forgotPasswordSubtitle')">
    <form @submit.prevent="onSubmit" class="form-grid">
      <label for="email">{{ t('auth.email') }}</label>
      <InputText id="email" v-model="email" :placeholder="t('auth.emailPlaceholder')" />
      <Button :label="t('auth.sendResetLink')" type="submit" class="p-button-primary" />
      <p class="helper-text">
        <RouterLink to="/signin" class="link">{{ t('auth.backToSignIn') }}</RouterLink>
      </p>
    </form>
  </AuthCard>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { forgotPassword } from '../../services/auth'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import AuthCard from '../../components/shared/AuthCard.vue'

const email = ref('')
const { t } = useI18n()

async function onSubmit() {
  try {
    await forgotPassword({ email: email.value })
    alert(t('auth.genericResetSuccess'))
  } catch (err) {
    console.error(err)
    alert(t('auth.requestFailed'))
  }
}
</script>

<style scoped>
.form-grid {
  display: grid;
  gap: 0.8rem;
}
.form-grid label {
  display: block;
  font-weight: 600;
  color: var(--text);
}
.form-grid :deep(.p-inputtext) {
  width: 100%;
  border-radius: 12px;
}
.helper-text {
  margin: 0.15rem 0 0;
  color: var(--muted);
  font-size: 0.95rem;
}
.link {
  color: var(--primary);
  text-decoration: none;
}
.link:hover {
  text-decoration: underline;
}
</style>
