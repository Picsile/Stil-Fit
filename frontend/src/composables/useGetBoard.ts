import { ref } from 'vue'
import { viewApi } from '@/api/api'

export function useGetBoard() {
  const isLoadBoard = ref(false)

  async function getBoard(boardId: number) {
    let board

    try {
      isLoadBoard.value = true
      const data = await viewApi.getBoard(boardId)

      // console.log(data)

      if (data?.status === 'success') {
        board = data.board
      }

      if (data?.status === 'error') {
        throw new Error(data?.message)
      }
    } catch (e) {
      console.error('Ошибка при получении доски: ', e)
    } finally {
      isLoadBoard.value = false
    }

    return board
  }

  return { isLoadBoard, getBoard }
}
