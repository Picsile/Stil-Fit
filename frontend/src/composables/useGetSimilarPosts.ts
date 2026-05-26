import { ref } from 'vue'
import { viewApi } from '@/api/api'

export function useGetSimilarPosts() {
  const isLoadPosts = ref(false)

  async function getSimilarPosts(postId: number, offset: number, limit: number) {
    let posts = []

    try {
      isLoadPosts.value = true
      const data = await viewApi.getSimilar(postId, offset, limit)

      if (data?.status === 'success') {
        posts = data.posts
      }
      
      if (data?.status === 'error') {
        throw new Error(data?.message)
      }

      return posts
    } catch (e) {
      console.error('Ошибка при получени похожих постов: ', e)
    } finally {
      isLoadPosts.value = false
    }
  }

  return { isLoadPosts, getSimilarPosts }
}
