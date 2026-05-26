import { ref } from 'vue'
import { viewApi } from '@/api/api'

export function useSearchPosts() {
  const isLoadSearchPosts = ref(false)

  async function getSearchPosts(query: string, offset: number, limit: number) {
    let posts = []

    try {
      isLoadSearchPosts.value = true
      const data = await viewApi.searchPosts(String(query).trim(), offset, limit)

      if (data?.status === 'success') {
        posts = data.posts
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при получении доски: ', e)
    } finally {
      isLoadSearchPosts.value = false
    }

    return posts
  }

  return { isLoadSearchPosts, getSearchPosts }
}
