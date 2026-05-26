import { authApi } from '@/api/api'
import type { UserData } from '@/types/auth.types'
import { defineStore } from 'pinia'
import { useBoardStore } from './board.stores'

export const useUserStore = defineStore('user', {
  state: () => {
    return {
      user: null as UserData | null,
      isAccount: false as boolean,
      isAdmin: false as boolean,
      isAuthLoaded: false as boolean,
    }
  },

  actions: {
    setUser(userData: UserData) {
      this.user = userData
      if (userData) {
        this.isAccount = true
        if (userData.role_id == 2) this.isAdmin = true
      }
    },

    clearUser() {
      this.user = null
      this.isAccount = false
      this.isAdmin = false

      const boardStore = useBoardStore()
      boardStore.clearBoards()
    },

    // Инициализация пользователя
    async initUser() {
      const token: string | null = localStorage.getItem('token')
      
      if (token) {
        try {
          const data = await authApi.findByToken(token)

          console.log('UserData', data)

          if (data?.status === 'success') {
            this.setUser(data.user)
          }

          if (data?.status === 'error') {
            localStorage.removeItem('token')
            throw new Error(data?.message)
          }
        } catch (e) {
          console.error('Ошибка при проверке токена: ', e)
        } finally {
          this.isAuthLoaded = true
        }
      }
    },
  },
})
