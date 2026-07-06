<script setup>

import {
  RouterLink,
  RouterView,
  useRoute,
  useRouter
} from 'vue-router'
import { onMounted } from 'vue'
import { computed, ref, watch } from 'vue'

import { useAuthStore } from './services/auth'
import './style/main.css'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const currentGroupId = computed(() => {
  return route.params.gruppeID ?? null
})

onMounted(async () => {
  await authStore.checkAuthentication
})


async function handleLogout() {
  
  await authStore.logout()
  await router.push('/login')
}
</script>

<template>
  <div class="app-shell">
    <header class="navbar">
      <div class="nav-container">
        <RouterLink to="/" class="brand">
          StudySprint
        </RouterLink>

        <nav
          v-if="authStore.isAuthenticated"
          class="main-nav"
        >
          <RouterLink to="/dashboard">
            Dashboard
          </RouterLink>

          <RouterLink to="/groups">
            Gruppen
          </RouterLink>

            <RouterLink to="/tasks">
          
            Aufgaben
          </RouterLink>

          <RouterLink to="/goals">
            Lernziele
          </RouterLink>

          <RouterLink to="/sprints">
            Sprints
          </RouterLink>

          <RouterLink to="/meetings">
            Termine
          </RouterLink>
        </nav>

        <div class="nav-actions">
          <template v-if="!authStore.isAuthenticated">
            <RouterLink
              to="/login"
              class="btn btn-outline"
            >
              Login
            </RouterLink>

            <RouterLink
              to="/register"
              class="btn btn-primary"
            >
              Registrieren
            </RouterLink>
          </template>

          <button
            v-else
            type="button"
            class="btn btn-outline"
            :disabled="authStore.loading"
            @click="handleLogout"
          >
            {{ authStore.loading ? 'Bitte warten...' : 'Logout' }}
          </button>
        </div>
      </div>
    </header>

    <main class="app-content">
      <RouterView />
    </main>

    <footer class="footer">
      <p>
        © 2026 StudySprint – Gemeinsam lernen, gemeinsam wachsen.
      </p>
    </footer>
  </div>
</template>


<style>
.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f3f4f6;
  font-family: 'Inter', sans-serif;
}

.navbar {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.nav-container {
  display: flex;
  align-items: center;
  gap: 32px;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.brand {
  font-size: 20px;
  font-weight: 800;
  color: #2563eb;
  text-decoration: none;
  letter-spacing: -0.5px;
  flex-shrink: 0;
}

.main-nav {
  display: flex;
  gap: 8px;
  flex: 1;
}

.main-nav a {
  text-decoration: none;
  color: #6b7280;
  font-weight: 500;
  font-size: 14px;
  padding: 6px 12px;
  border-radius: 8px;
  transition: all 0.2s;
}

.main-nav a:hover {
  background: #eff6ff;
  color: #2563eb;
}

.main-nav a.router-link-active {
  background: #eff6ff;
  color: #2563eb;
  font-weight: 600;
}

.nav-actions {
  display: flex;
  gap: 12px;
  margin-left: auto;
}

.btn {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  display: inline-flex;
  align-items: center;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-outline {
  background: white;
  color: #2563eb;
  border: 1px solid #2563eb;
}

.btn-outline:hover {
  background: #eff6ff;
}

.app-content {
  flex: 1;
  padding: 32px 24px;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  box-sizing: border-box;
}

.footer {
  background: white;
  border-top: 1px solid #e5e7eb;
  text-align: center;
  padding: 20px;
  color: #9ca3af;
  font-size: 13px;
}

@media (max-width: 768px) {
  .main-nav {
    display: none;
  }
}
</style>