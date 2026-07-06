import api from './apiService'

export async function login(email, password) {
  try {
    const response = await api.post('/login', {
      email,
      password,
    })

    return response.data
  } catch (error) {
    console.error(
      'Fehler beim Login:',
      error.response?.status,
      error.response?.data
    )

    throw error
  }
}

export async function register(
  name,
  email,
  password,
  passwordConfirmation
) {
  try {
    const response = await api.post('/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    })

    return response.data
  } catch (error) {
    console.error(
      'Fehler bei der Registrierung:',
      error.response?.status,
      error.response?.data
    )

    throw error
  }
}

export async function logout() {
  const token = localStorage.getItem('token')

  if (!token) {
    return null
  }

  const response = await api.post('/logout')

  return response.data
}

export function isLoggedIn() {
  return Boolean(localStorage.getItem('token'))
}

export async function getCurrentUser() {
  const token = localStorage.getItem('token')

  if (!token) {
    return null
  }

  const response = await api.get('/me')

  return response.data
}