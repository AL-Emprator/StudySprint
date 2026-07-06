
import { defineStore } from 'pinia'
import { 
  getCurrentUser, 
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest
} from '../services/authService'




// useAuthStore is a Pinia store that manages authentication state and actions for the application. 
// It provides state variables, getters, and actions related to user authentication.
export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    loading: false,
    error: null,
  }),

  //In this case, isAuthenticated returns true if a token exists, indicating that the user is logged in.
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },

  // The actions section contains methods that can be called to perform authentication-related tasks, such as logging in, logging out, and checking authentication status.
  actions: {
    // setAuthentication is a method that sets the authentication state by storing the token and user information in the store and localStorage.
    setAuthentication(data) {
      this.token = data.token
      this.user = data.user ?? null

      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(this.user)
      )
    },

    clearAuthentication() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },


    async checkAuthentication() {
      if (!this.token) {
        this.user = null
        return false
      }

      try {
        this.user = await getCurrentUser()
        return true
      } catch (error) {
        this.clearAuthentication()
        return false
      }
    },


    async register(name, email, password, passwordConfirmation) {
    this.loading = true
    this.error = null

    try {
        const data = await registerRequest(
        name,
        email,
        password,
        passwordConfirmation
        )

        this.setAuthentication(data)

        return data
    } catch (error) {
        this.error =
        error.response?.data?.message ??
        'Die Registrierung ist fehlgeschlagen.'

        throw error
    } finally {
        this.loading = false
    }
    },

    // login is an asynchronous method that sends a login request to the backend API with the provided email and password.
    async login(email, password) {
      this.loading = true
      this.error = null

      try {
        
        const data = await loginRequest(email, password)

        this.setAuthentication(data)

        return data
        
      } catch (error) {
        this.error =
          error.response?.data?.message ??
          'Die Anmeldung ist fehlgeschlagen.'

        throw error
      } finally {
        this.loading = false
      }
    },


    async logout() {
        this.loading = true
        this.error = null

        try {
            if (this.token) {
            await logoutRequest()
            }
        } catch (error) {
            console.error(
            'Fehler beim Logout:',
            error.response?.status,
            error.response?.data
            )
        } finally {
            this.clearAuthentication()
            this.loading = false
        }
        },
    },

   




})