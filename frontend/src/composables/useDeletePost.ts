import { ref } from 'vue'
import { postApi } from '@/api/api'

export function useDeletePost() {
  const isLoadDeletePost = ref(false)

  const deletePost = async (postId: number) => {

    try {
      isLoadDeletePost.value = true
      const data = await postApi.deletePost(postId)
      console.log(data)

      if (data?.status === 'success') {
        return true
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка удаления поста: ', e)
    } finally {
      isLoadDeletePost.value = false
    }
  }

  return { isLoadDeletePost, deletePost }
}
