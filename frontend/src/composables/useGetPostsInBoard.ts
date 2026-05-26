import { ref } from 'vue'
import { viewApi } from '@/api/api'
import type { PostPreviewData } from '@/types/view.types'

export function useGetPostsInBoard() {
  const isLoadPostsInBoard = ref(false)

  const getPostsInBoard = async (boardId: number, offset: number, limit: number) => {
    let posts: PostPreviewData[] = []

    try {
      isLoadPostsInBoard.value = true
      const data = await viewApi.getPostsInBoard(boardId, offset, limit)

      console.log(data, offset)

      if (data?.status === 'success') {
        posts = data.posts
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при получении постов для доски: ', e)
    } finally {
      isLoadPostsInBoard.value = false
    }

    return posts
  }

  return { isLoadPostsInBoard, getPostsInBoard }
}
