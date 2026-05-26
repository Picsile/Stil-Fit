import { ref } from 'vue'
import { viewApi } from '@/api/api'
import type { ProfileData } from '@/types/view.types'

export function useGetProfile() {
  const isLoadProfile = ref(false)

  const getProfile = async (userId: number): Promise<ProfileData | null> => {
    try {
      isLoadProfile.value = true
      const data = await viewApi.getProfile(userId)

      if (data?.status === 'success') {
        return data.profile
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при получении профиля: ', e)
    } finally {
      isLoadProfile.value = false
    }

    return null
  }

  return { isLoadProfile, getProfile }
}