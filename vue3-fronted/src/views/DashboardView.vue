<template>
  <div class="dashboard-page">
    <div class="container py-4">
      <!-- Welcome Header -->
      <div class="welcome-header mb-4">
        <div class="user-greeting">
          <div class="user-avatar">{{ user.initials }}</div>
          <div>
            <h1 class="welcome-title">Welcome back, {{ user.displayName }}!</h1>
            <p class="member-since">
              <i :class="tenantBranding.logoIcon" class="me-1"></i>
              Workspace: <strong>{{ tenantName }}</strong> &bull;
              <i class="pi pi-calendar ms-2 me-1"></i>
              Member since: {{ user.formattedCreatedAt }}
            </p>
          </div>
        </div>
        <div class="header-actions">
          <button class="btn btn-outline-danger btn-sm" @click="handleLogout">
            <i class="pi pi-sign-out me-1"></i>
            Logout
          </button>
        </div>
      </div>

      <!-- Core Status Cards -->
      <div class="grid-cards mb-4">
        <!-- Account Details Card -->
        <div class="card card-account">
          <div class="card-header bg-primary text-white">
            <i class="pi pi-user me-2"></i>
            Account Overview
          </div>
          <div class="card-body">
            <div class="info-row">
              <span class="label">Name:</span>
              <span class="value fw-bold">{{ user.name || 'Not specified' }}</span>
            </div>
            <div class="info-row">
              <span class="label">Email:</span>
              <span class="value">{{ user.email }}</span>
            </div>
            <div class="info-row mt-2">
              <span class="label">Status:</span>
              <span v-if="user.isVerified" class="badge bg-success">
                <i class="pi pi-check-circle me-1"></i> Verified
              </span>
              <span v-else class="badge bg-warning text-dark">
                <i class="pi pi-clock me-1"></i> Pending Verification
              </span>
            </div>
          </div>
        </div>

        <!-- Session Status Card -->
        <div class="card card-session">
          <div class="card-header bg-success text-white">
            <i class="pi pi-shield me-2"></i>
            Active Session
          </div>
          <div class="card-body">
            <div class="info-row">
              <span class="label">Session ID:</span>
              <code class="session-code">{{ sessionId || 'Active Redis Session' }}</code>
            </div>
            <div class="info-row mt-2">
              <span class="label">Page Visits:</span>
              <span class="badge bg-info text-dark fs-6">{{ visits }}</span>
            </div>
          </div>
        </div>

        <!-- Quick System Tests Card -->
        <div class="card card-tests">
          <div class="card-header bg-info text-white">
            <i class="pi pi-bolt me-2"></i>
            System Quick Tests
          </div>
          <div class="card-body">
            <div class="buttons-group mb-3">
              <button
                class="btn btn-sm btn-outline-primary"
                :disabled="isPinging"
                @click="runPing"
              >
                <span v-if="isPinging" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="pi pi-compass me-1"></i>
                Ping API
              </button>

              <button
                class="btn btn-sm btn-outline-success"
                :disabled="isTestingDb"
                @click="runDbTest"
              >
                <span v-if="isTestingDb" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="pi pi-database me-1"></i>
                Test DB
              </button>

              <button
                class="btn btn-sm btn-outline-danger"
                :disabled="isTestingRedis"
                @click="runRedisTest"
              >
                <span v-if="isTestingRedis" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="pi pi-server me-1"></i>
                Test Redis
              </button>
            </div>

            <!-- Dynamic Live Output Console -->
            <div v-if="testResult" class="console-box">
              <div class="console-header">
                <span class="console-dot red"></span>
                <span class="console-dot yellow"></span>
                <span class="console-dot green"></span>
                <span class="console-title">Live API Output</span>
              </div>
              <pre class="console-body">{{ testResult }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useDashboardController } from '../controllers/DashboardController'
import { useAuthController } from '../controllers/AuthController'
import { useTenantStore } from '../store/tenantStore'

const tenantStore = useTenantStore()
const tenantName = computed(() => tenantStore.activeTenant.name)
const tenantBranding = computed(() => tenantStore.branding)

const {
  user,
  sessionId,
  visits,
  isTestingDb,
  isTestingRedis,
  isPinging,
  testResult,
  runDbTest,
  runRedisTest,
  runPing
} = useDashboardController()

const { submitLogout } = useAuthController()

function handleLogout() {
  submitLogout()
}
</script>

<style scoped>
.dashboard-page {
  min-height: calc(100vh - 120px);
  background-color: var(--surface, #f8fafc);
  color: var(--text, #1e293b);
  padding-bottom: 2rem;
}

.container {
  max-width: 1140px;
  margin: 0 auto;
  padding: 1.5rem;
}

.welcome-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  background: var(--surface-elevated, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.user-greeting {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary, #0f66f0), var(--accent, #6366f1));
  color: #fff;
  font-weight: 700;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.welcome-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.member-since {
  margin: 0.25rem 0 0;
  color: var(--muted, #64748b);
  font-size: 0.9rem;
}

.grid-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.5rem;
}

.card {
  background: var(--surface-elevated, #ffffff);
  border: 1px solid var(--border, #e2e8f0);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
}

.card-header {
  padding: 0.75rem 1rem;
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
}

.card-body {
  padding: 1.25rem;
  flex: 1;
}

.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.label {
  color: var(--muted, #64748b);
}

.session-code {
  background: rgba(0, 0, 0, 0.05);
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  font-size: 0.85rem;
  word-break: break-all;
}

.buttons-group {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.4rem 0.8rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 6px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.825rem;
}

.btn-outline-primary {
  color: #0d6efd;
  border-color: #0d6efd;
  background: transparent;
}
.btn-outline-primary:hover:not(:disabled) {
  background: #0d6efd;
  color: #fff;
}

.btn-outline-success {
  color: #198754;
  border-color: #198754;
  background: transparent;
}
.btn-outline-success:hover:not(:disabled) {
  background: #198754;
  color: #fff;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
  background: transparent;
}
.btn-outline-danger:hover:not(:disabled) {
  background: #dc3545;
  color: #fff;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25em 0.5em;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 0.25rem;
}

.bg-primary { background-color: #0d6efd !important; }
.bg-success { background-color: #198754 !important; }
.bg-info { background-color: #0dcaf0 !important; }
.bg-warning { background-color: #ffc107 !important; }
.text-dark { color: #000 !important; }
.text-white { color: #fff !important; }

.console-box {
  background: #1e1e2e;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 0.75rem;
}

.console-header {
  background: #181825;
  padding: 0.35rem 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.console-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.console-dot.red { background: #ff5f56; }
.console-dot.yellow { background: #ffbd2e; }
.console-dot.green { background: #27c93f; }

.console-title {
  margin-left: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

.console-body {
  margin: 0;
  padding: 0.75rem;
  color: #a6e3a1;
  font-family: monospace;
  font-size: 0.8rem;
  max-height: 150px;
  overflow-y: auto;
  white-space: pre-wrap;
}
</style>
