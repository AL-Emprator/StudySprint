<template>
  <div class="login-page">
    <div class="login-card">
      <h1>Login</h1>

      <p class="subtitle">
        Melde dich mit deinem Account an.
      </p>

      <form @submit.prevent="handleLogin">
        <label for="email">E-Mail</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="max@example.com"
          required
        />

        <label for="password">Passwort</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          placeholder="Passwort eingeben"
          required
        />

        <p v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Bitte warten...' : 'Einloggen' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '../services/authService'
import { useAuthStore } from '../services/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const isLoading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(form.email, form.password)
    await router.push('/dashboard')
  } catch {
    errorMessage.value =
      authStore.error ?? 'Login fehlgeschlagen.'
  } finally {
    isLoading.value = false
  }
}

</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  padding-top: 60px;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: white;
  padding: 24px;
  border-radius: 14px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
}

h1 {
  margin-top: 0;
  margin-bottom: 8px;
}

.subtitle {
  margin-bottom: 24px;
  color: #6b7280;
}

form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

label {
  font-weight: 600;
}

input {
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
}

button {
  margin-top: 8px;
  padding: 12px;
  border: 0;
  border-radius: 8px;
  background: #2563eb;
  color: white;
  font-size: 15px;
  cursor: pointer;
}

button:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}

.error-message {
  color: #b91c1c;
  background: #fee2e2;
  padding: 10px;
  border-radius: 8px;
}
</style>