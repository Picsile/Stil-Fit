import { ref } from 'vue'
import { accountApi } from '@/api/api'
import { useAuthRedirect } from './useAuthRedirect'
import type { BoardId } from '@/types/board.types'

export function useSavePostToBoard() {
  const saveBoardIds = ref<BoardId[]>([])

  const savePost = async (boardId: BoardId, id: number) => {
    const index = saveBoardIds.value.findIndex((bid) => bid === boardId)
    const prev = [...saveBoardIds.value]

    if (index === -1) {
      saveBoardIds.value.push(boardId)
    } else {
      saveBoardIds.value.splice(index, 1)
    }

    try {
      const data = await accountApi.savePost(boardId, id)

      if (data?.status === 'error') {
        saveBoardIds.value = prev
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при сохранении поста: ', e)
    }
  }

  return { saveBoardIds, savePost }
}
