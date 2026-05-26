import { ref } from 'vue'
import { viewApi } from '@/api/api'
import type { PostPreviewData } from '@/types/view.types'

export function useGetPostsSystemBoard() {
  const isLoadPostsSystemBoard = ref(false)

  const getPostsSystemBoard = async (
    boardId: string,
    offset: number,
    limit: number,
    userId?: number,
  ) => {
    let posts: PostPreviewData[] = []

    try {
      isLoadPostsSystemBoard.value = true
      const data = await viewApi.getPostsSystemBoard(boardId, offset, limit, userId)

      console.log(data)

      if (data?.status === 'success') {
        posts = data.posts
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при получении постов системной доски: ', e)
    } finally {
      isLoadPostsSystemBoard.value = false
    }

    return posts
  }

  return { isLoadPostsSystemBoard, getPostsSystemBoard }
}
