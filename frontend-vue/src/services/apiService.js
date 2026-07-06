// src/services/apiService.js

import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// Interceptor, um den Authorization-Header mit dem Token aus localStorage hinzuzufügen
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

// Funktion, um API-Anfragen zu senden
export async function request(endpoint, options = {}) {
  const response = await api.request({
    url: endpoint,
    method: options.method ?? 'GET',
    data: options.body,
    headers: options.headers,
  })

  return response.data
}

export default api