import type { Ref } from 'vue'
import { accountApi } from '@/api/api'
import type { PostData } from '@/types/view.types'
import { useAuthRedirect } from './useAuthRedirect'

export function usePostLike(post: Ref<PostData | undefined>) {
  const { requireAuth } = useAuthRedirect()

  const like = async (postId: number) => {
    if (!requireAuth()) return

    try {
      if (post.value) {
        if (post.value.is_liked) {
          post.value.is_liked = false
          post.value.likes_count--
        } else {
          post.value.is_liked = true
          post.value.likes_count++
        }
      }

      const data = await accountApi.like(postId)

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при добавлении лайка: ', e)
    }
  }

  return { like }
}
