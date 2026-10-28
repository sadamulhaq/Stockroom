import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/client'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('inv_token'))
  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('inv_token', data.token)
  }

  async function register(name: string, email: string, password: string, password_confirmation: string) {
    const { data } = await api.post('/auth/register', { name, email, password, password_confirmation })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('inv_token', data.token)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('inv_token')
    }
  }

  async function fetchMe() {
    try {
      const { data } = await api.get('/auth/me')
      user.value = data.data ?? data
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('inv_token')
    }
  }

  return { user, token, isAuthenticated, login, register, logout, fetchMe }
})
