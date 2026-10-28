import axios from 'axios'
import type { Router } from 'vue-router'

let router: Router | null = null
let isRedirecting = false

export function setRouter(r: Router) {
  router = r
}

export function stripEmpty(params: Record<string, unknown>): Record<string, unknown> {
  const cleaned: Record<string, unknown> = {}
  for (const [key, value] of Object.entries(params)) {
    if (value !== '' && value !== null && value !== undefined) {
      cleaned[key] = value
    }
  }
  return cleaned
}

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('inv_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && !isRedirecting) {
      isRedirecting = true
      localStorage.removeItem('inv_token')
      if (router) {
        router.replace('/login').finally(() => {
          isRedirecting = false
        })
      } else {
        isRedirecting = false
      }
    }
    return Promise.reject(error)
  },
)

export default api
