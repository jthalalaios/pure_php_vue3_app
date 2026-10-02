<template>
  <div class="verify-page">
    <div class="card-verify">
      <div v-if="isLoading" class="loading-state">
        <span class="spinner-border text-primary mb-3"></span>
        <h2>Verifying your email...</h2>
        <p class="text-muted">Please wait while we confirm your verification token.</p>
      </div>

      <div v-else-if="isVerified" class="success-state">
        <div class="icon-circle success">
          <i class="pi pi-check"></i>
        </div>
        <h2>Email Verified!</h2>
        <p class="message">{{ message || 'Your email address has been successfully verified.' }}</p>
        <RouterLink to="/signin" class="btn btn-primary mt-3">
          Continue to Sign In
        </RouterLink>
      </div>

      <div v-else class="error-state">
        <div class="icon-circle error">
          <i class="pi pi-times"></i>
        </div>
        <h2>Verification Failed</h2>
        <p class="message">{{ message || 'This verification link is invalid or has expired.' }}</p>
        <RouterLink to="/register" class="btn btn-outline-secondary mt-3">
          Back to Registration
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { authService } from '../services/auth.service'

const route = useRoute()
const isLoading = ref(true)
const isVerified = ref(false)
const message = ref('')

onMounted(async () => {
  const token = String(route.query.token || '')
  if (!token) {
    isLoading.value = false
    isVerified.value = false
    message.value = 'No verification token provided in the link.'
    return
  }

  try {
    const res = await authService.verifyEmail(token)
    isVerified.value = res.verified
    message.value = res.message
  } catch (err: any) {
    isVerified.value = false
    message.value = err.message || 'Verification failed.'
  } finally {
    isLoading.value = false
  }
})
</script>

<style scoped>
.verify-page {
  min-height: calc(100vh - 120px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: var(--surface, #f8fafc);
}

.card-verify {
  max-width: 480px;
  width: 100%;
  background: var(--surface-elevated, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 16px;
  padding: 2.5rem 2rem;
  text-align: center;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
}

.icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin-bottom: 1.25rem;
}

.icon-circle.success {
  background: #dcfce7;
  color: #16a34a;
}

.icon-circle.error {
  background: #fee2e2;
  color: #dc2626;
}

h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.message {
  color: var(--muted, #64748b);
  margin-bottom: 1.5rem;
}

.btn {
  display: inline-block;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: var(--primary, #0f66f0);
  color: #fff;
}
.btn-primary:hover {
  opacity: 0.9;
}

.btn-outline-secondary {
  border: 1px solid var(--border, #cbd5e1);
  color: var(--text, #334155);
}
</style>
