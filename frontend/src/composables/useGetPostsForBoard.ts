import { ref } from 'vue'
import { viewApi } from '@/api/api'
import type { PostPreviewData } from '@/types/view.types'

export function useGetPostsForBoard() {
  const isLoadPostsForBoard = ref(false)

  const getPostsForBoard = async (boardId: number, limit: number) => {
    let posts: PostPreviewData[] = []

    try {
      isLoadPostsForBoard.value = true
      const data = await viewApi.getPostsForBoard(boardId, limit)

      if (data?.status === 'success') {
        return data.posts
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при получении постов для доски: ', e)
    } finally {
      isLoadPostsForBoard.value = false
    }

    return posts
  }

  return { isLoadPostsForBoard, getPostsForBoard }
}
