<template>
  <div class="register-page">
    <div class="register-card">
      <h1>Registrierung</h1>

      <p class="subtitle">
        Erstelle einen neuen Account.
      </p>

      <form @submit.prevent="handleRegister">
        <label for="name">Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          placeholder="Max Mustermann"
          required
        />

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

        <label for="passwordConfirmation">
          Passwort wiederholen
        </label>
        <input
          id="passwordConfirmation"
          v-model="form.passwordConfirmation"
          type="password"
          placeholder="Passwort wiederholen"
          required
        />

        <p v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </p>

        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Bitte warten...' : 'Registrieren' }}
        </button>
      </form>

      <p class="login-link">
        Bereits registriert?
        <RouterLink to="/login">
          Zum Login
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { register } from '../services/authService'

const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})

const isLoading = ref(false)
const errorMessage = ref('')

async function handleRegister() {
  errorMessage.value = ''

  if (form.password !== form.passwordConfirmation) {
    errorMessage.value = 'Die Passwörter stimmen nicht überein.'
    return
  }

  if (form.password.length < 8) {
    errorMessage.value =
      'Das Passwort muss mindestens 8 Zeichen lang sein.'
    return
  }

  isLoading.value = true

  try {
    await register(
      form.name,
      form.email,
      form.password,
      form.passwordConfirmation
    )

    await router.push('/dashboard')
  } catch (error) {
    if (error.response?.status === 409) {
      errorMessage.value =
        'Ein Benutzer mit dieser E-Mail-Adresse existiert bereits.'
    } else if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      errorMessage.value =
        errors?.email?.[0] ??
        errors?.password?.[0] ??
        errors?.name?.[0] ??
        'Bitte überprüfe deine Eingaben.'
    } else if (error.response?.status === 404) {
      errorMessage.value =
        'Die Registrierungsroute wurde im Backend nicht gefunden.'
    } else {
      errorMessage.value = 'Die Registrierung ist fehlgeschlagen.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.register-page {
  display: flex;
  justify-content: center;
  padding-top: 60px;
}

.register-card {
  width: 100%;
  max-width: 460px;
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

.login-link {
  margin-top: 18px;
  color: #4b5563;
}
</style>