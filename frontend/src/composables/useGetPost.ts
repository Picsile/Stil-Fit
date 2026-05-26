import { ref } from 'vue'
import { viewApi } from '@/api/api'

export function useGetPost() {
  const isLoadPost = ref(false)

  async function getPost(postId: number) {
    let post

    try {
      isLoadPost.value = true
      const data = await viewApi.getPost(postId)
      
      console.log(data)

      if (data?.status === 'success') {
        post = data.post
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }

      return post
    } catch (e) {
      console.error('Ошибка при получении поста: ', e)
    } finally {
      isLoadPost.value = false
    }
  }

  return { isLoadPost, getPost }
}
