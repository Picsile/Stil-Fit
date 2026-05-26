import { ref } from 'vue'
import { viewApi } from '@/api/api'

export function useGetOutfitsWithItem() {
  const isLoadOutfits = ref(false)

  async function getOutfitsWithItem(itemId: number, offset: number, limit: number) {
    let outfits = []
    try {
      isLoadOutfits.value = true
      const data = await viewApi.getOutfitsWithItem(itemId, offset, limit)

      if (data?.status === 'success') {
        outfits = data.outfits
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }

      return outfits
    } catch (e) {
      console.error('Ошибка при получении образов: ', e)
    } finally {
      isLoadOutfits.value = false
    }
  }

  return { isLoadOutfits, getOutfitsWithItem }
}
