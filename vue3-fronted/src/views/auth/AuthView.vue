<template>
  <div class="auth-wrapper">
    <div class="auth-card-dynamic">
      <!-- Dynamic Mode Switcher Header -->
      <div class="tabs-header">
        <button
          :class="['tab-btn', { active: currentMode === 'login' }]"
          @click="switchMode('login')"
        >
          {{ t('auth.signInTitle') }}
        </button>
        <button
          :class="['tab-btn', { active: currentMode === 'register' }]"
          @click="switchMode('register')"
        >
          {{ t('auth.createAccount') }}
        </button>
      </div>

      <!-- Flash / Alert Messages -->
      <div v-if="generalError" class="alert alert-danger alert-dismissible fade show">
        <i class="pi pi-exclamation-triangle me-2"></i>
        {{ generalError }}
        <button type="button" class="btn-close" @click="clearErrors"></button>
      </div>

      <div v-if="successMessage" class="alert alert-success alert-dismissible fade show">
        <i class="pi pi-check-circle me-2"></i>
        {{ successMessage }}
      </div>

      <!-- Sign In Form -->
      <form v-if="currentMode === 'login'" @submit.prevent="handleLogin" class="form-content" novalidate>
        <div class="form-group">
          <label for="login-email">{{ t('auth.email') }}</label>
          <div class="input-with-icon">
            <i class="pi pi-envelope input-icon"></i>
            <input
              id="login-email"
              type="email"
              v-model="loginForm.email"
              :class="['form-input', { 'is-invalid': fieldErrors.email }]"
              :placeholder="t('auth.emailPlaceholder')"
              autocomplete="email"
              required
            />
          </div>
          <div v-if="fieldErrors.email" class="invalid-feedback">{{ fieldErrors.email }}</div>
        </div>

        <div class="form-group">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="login-password">{{ t('auth.password') }}</label>
            <RouterLink to="/forgot" class="small-link">{{ t('auth.forgotPassword') }}</RouterLink>
          </div>
          <div class="input-group">
            <div class="input-with-icon flex-grow-1">
              <i class="pi pi-lock input-icon"></i>
              <input
                id="login-password"
                :type="showPassword ? 'text' : 'password'"
                v-model="loginForm.password"
                :class="['form-input', { 'is-invalid': fieldErrors.password }]"
                :placeholder="t('auth.passwordPlaceholder')"
                autocomplete="current-password"
                required
              />
            </div>
            <button
              type="button"
              class="btn-toggle-pw"
              @click="togglePasswordVisibility"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
            </button>
          </div>
          <div v-if="fieldErrors.password" class="invalid-feedback">{{ fieldErrors.password }}</div>
        </div>

        <button type="submit" class="btn-submit" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
          {{ t('auth.signInTitle') }}
        </button>

        <p class="switch-helper">
          {{ t('auth.newHere') }}
          <a href="#" @click.prevent="switchMode('register')" class="accent-link">
            {{ t('auth.createAccount') }}
          </a>
        </p>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="form-content" novalidate>
        <div class="form-group">
          <label for="reg-name">{{ t('auth.name') }}</label>
          <div class="input-with-icon">
            <i class="pi pi-user input-icon"></i>
            <input
              id="reg-name"
              type="text"
              v-model="registerForm.name"
              :class="['form-input', { 'is-invalid': fieldErrors.name }]"
              :placeholder="t('auth.fullName')"
              autocomplete="name"
              required
            />
          </div>
          <div v-if="fieldErrors.name" class="invalid-feedback">{{ fieldErrors.name }}</div>
        </div>

        <div class="form-group">
          <label for="reg-email">{{ t('auth.email') }}</label>
          <div class="input-with-icon">
            <i class="pi pi-envelope input-icon"></i>
            <input
              id="reg-email"
              type="email"
              v-model="registerForm.email"
              :class="['form-input', { 'is-invalid': fieldErrors.email }]"
              :placeholder="t('auth.emailPlaceholder')"
              autocomplete="email"
              required
            />
          </div>
          <div v-if="fieldErrors.email" class="invalid-feedback">{{ fieldErrors.email }}</div>
        </div>

        <div class="form-group">
          <label for="reg-password">{{ t('auth.password') }}</label>
          <div class="input-group">
            <div class="input-with-icon flex-grow-1">
              <i class="pi pi-lock input-icon"></i>
              <input
                id="reg-password"
                :type="showPassword ? 'text' : 'password'"
                v-model="registerForm.password"
                :class="['form-input', { 'is-invalid': fieldErrors.password }]"
                :placeholder="t('auth.choosePassword')"
                autocomplete="new-password"
                required
              />
            </div>
            <button
              type="button"
              class="btn-toggle-pw"
              @click="togglePasswordVisibility"
            >
              <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
            </button>
          </div>
          <div v-if="fieldErrors.password" class="invalid-feedback">{{ fieldErrors.password }}</div>

          <!-- Dynamic Live Password Strength Meter (from original app.js) -->
          <div v-if="registerForm.password" class="password-meter-wrap mt-2">
            <div class="progress-bar-bg">
              <div
                class="progress-bar-fill"
                :style="{ width: `${passwordStrength.percent}%` }"
                :class="passwordStrength.colorClass"
              ></div>
            </div>
            <div class="strength-caption">
              Strength: <span class="fw-bold">{{ passwordStrength.label || 'Too short' }}</span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="reg-password-confirm">Confirm Password</label>
          <div class="input-with-icon">
            <i class="pi pi-shield input-icon"></i>
            <input
              id="reg-password-confirm"
              :type="showPassword ? 'text' : 'password'"
              v-model="registerForm.password_confirm"
              :class="['form-input', { 'is-invalid': !isPasswordMatch || fieldErrors.password_confirm }]"
              placeholder="Re-enter your password"
              autocomplete="new-password"
              required
            />
          </div>
          <div v-if="!isPasswordMatch" class="invalid-feedback">Passwords do not match.</div>
          <div v-else-if="fieldErrors.password_confirm" class="invalid-feedback">{{ fieldErrors.password_confirm }}</div>
        </div>

        <button type="submit" class="btn-submit btn-success-custom" :disabled="isSubmitting">
          <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
          {{ t('auth.createAccount') }}
        </button>

        <p class="switch-helper">
          {{ t('auth.alreadyHaveAccount') }}
          <a href="#" @click.prevent="switchMode('login')" class="accent-link">
            {{ t('auth.signInTitle') }}
          </a>
        </p>
      </form>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthController } from '../../controllers/AuthController'

const props = defineProps({
  initialMode: {
    type: String as () => 'login' | 'register',
    default: 'login'
  }
})

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const currentMode = ref<'login' | 'register'>(props.initialMode || (route.path.includes('register') ? 'register' : 'login'))

watch(
  () => route.path,
  (path: string) => {
    currentMode.value = path.includes('register') ? 'register' : 'login'
    clearErrors()
  }
)

const {
  loginForm,
  registerForm,
  showPassword,
  togglePasswordVisibility,
  passwordStrength,
  isPasswordMatch,
  isSubmitting,
  generalError,
  successMessage,
  fieldErrors,
  clearErrors,
  submitLogin,
  submitRegister
} = useAuthController()

function switchMode(mode: 'login' | 'register') {
  currentMode.value = mode
  clearErrors()
  if (mode === 'login' && route.path !== '/signin') {
    router.replace('/signin')
  } else if (mode === 'register' && route.path !== '/register') {
    router.replace('/register')
  }
}

async function handleLogin() {
  await submitLogin()
}

async function handleRegister() {
  await submitRegister(() => {
    // Switch to login after successful register
    setTimeout(() => {
      switchMode('login')
    }, 2000)
  })
}
</script>

<style scoped>
.auth-wrapper {
  min-height: calc(100vh - 120px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background-color: var(--surface, #f8fafc);
}

.auth-card-dynamic {
  width: 100%;
  max-width: 440px;
  background: var(--surface-elevated, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
}

.tabs-header {
  display: flex;
  background: rgba(0, 0, 0, 0.04);
  padding: 4px;
  border-radius: 10px;
  margin-bottom: 1.5rem;
}

.tab-btn {
  flex: 1;
  padding: 0.6rem 1rem;
  border: none;
  background: transparent;
  color: var(--muted, #64748b);
  font-weight: 600;
  font-size: 0.95rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: var(--surface-elevated, #ffffff);
  color: var(--primary, #0f66f0);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 0.4rem;
  color: var(--text, #1e293b);
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: var(--muted, #94a3b8);
  font-size: 1rem;
}

.form-input {
  width: 100%;
  padding: 0.65rem 1rem 0.65rem 2.6rem;
  border: 1px solid var(--border, #cbd5e1);
  border-radius: 8px;
  font-size: 0.95rem;
  background: var(--surface, #fff);
  color: var(--text, #1e293b);
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary, #0f66f0);
  box-shadow: 0 0 0 3px rgba(15, 102, 240, 0.15);
}

.form-input.is-invalid {
  border-color: #dc3545;
}

.input-group {
  display: flex;
}

.input-group .form-input {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

.btn-toggle-pw {
  background: var(--surface, #f1f5f9);
  border: 1px solid var(--border, #cbd5e1);
  border-left: none;
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
  padding: 0 1rem;
  color: var(--muted, #64748b);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-toggle-pw:hover {
  background: rgba(0, 0, 0, 0.05);
}

.invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 0.8rem;
  margin-top: 0.25rem;
}

.password-meter-wrap {
  margin-top: 0.5rem;
}

.progress-bar-bg {
  width: 100%;
  height: 6px;
  background: rgba(0, 0, 0, 0.08);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  transition: width 0.3s ease, background-color 0.3s ease;
}

.bg-danger { background-color: #dc3545; }
.bg-warning { background-color: #ffc107; }
.bg-info { background-color: #0dcaf0; }
.bg-success { background-color: #198754; }

.strength-caption {
  font-size: 0.75rem;
  color: var(--muted, #64748b);
  margin-top: 0.25rem;
}

.btn-submit {
  width: 100%;
  padding: 0.75rem 1rem;
  background: var(--primary, #0f66f0);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s ease, transform 0.1s ease;
  margin-top: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.92;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-success-custom {
  background: #198754;
}

.switch-helper {
  text-align: center;
  margin: 1.25rem 0 0;
  font-size: 0.9rem;
  color: var(--muted, #64748b);
}

.accent-link, .small-link {
  color: var(--primary, #0f66f0);
  text-decoration: none;
  font-weight: 500;
}

.accent-link:hover, .small-link:hover {
  text-decoration: underline;
}

.small-link {
  font-size: 0.8rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.25rem;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
}

.alert-danger {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.alert-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.btn-close {
  background: transparent;
  border: none;
  margin-left: auto;
  cursor: pointer;
  color: inherit;
}
</style>
