import { accountApi, viewApi } from '@/api/api'
import type { BoardId, BoardPreviewData, BoardTitleData } from '@/types/board.types'
import { systemBoards } from '@/options/options'
import { defineStore } from 'pinia'
import { computed } from 'vue'

export const useBoardStore = defineStore('board', {
  state: () => {
    return {
      boards: [] as BoardPreviewData[],
      isLoaded: false as boolean,
      isCreateModalOpen: false as boolean,
      isDeleteModalOpen: false as boolean,
      postAddBoardId: undefined as number | undefined,
      deleteBoardId: undefined as number | undefined,
    }
  },

  getters: {
    boardTitles(): BoardTitleData[] {
      return [
        ...systemBoards,
        ...this.boards.map((board) => ({
          id: board.id,
          title: board.title,
        })),
      ]
    },
  },

  actions: {
    async getBoards(userId: number) {
      try {
        const data = await viewApi.getBoards(userId)

        if (data?.status === 'success') {
          this.boards = data.boards
          this.isLoaded = true
        }

        if (data?.status === 'error') {
          throw new Error(data?.message)
        }
      } catch (e) {
        console.error('Ошибка при получении досок: ', e)
      }
    },

    async savePost(boardId: BoardId, postId: number) {
      try {
        const data = await accountApi.savePost(boardId, postId)

        if (data?.status === 'error') {
          throw new Error(data?.message)
        }

        return data
      } catch (e) {
        console.error('Ошибка при сохранении поста: ', e)
        return { status: 'error' }
      }
    },

    async createBoard(formData: FormData) {
      try {
        const data = await accountApi.createBoard(formData)

        if (data.status === 'success') {
          this.boards.push(data.board)
          this.closeCreateModal()
          return { status: 'success', board: data.board }
        }

        return data
      } catch (error) {
        console.error('Ошибка при создании доски:', error)
        return { status: 'error', message: 'Ошибка при создании доски' }
      }
    },

    openCreateModal(postId?: number) {
      this.postAddBoardId = postId
      this.isCreateModalOpen = true
    },

    closeCreateModal() {
      this.isCreateModalOpen = false
      this.postAddBoardId = undefined
    },

    openDeleteModal(boardId: number) {
      this.deleteBoardId = boardId
      this.isDeleteModalOpen = true
    },

    closeDeleteModal() {
      this.isDeleteModalOpen = false
      this.deleteBoardId = undefined
    },

    async deleteBoard(boardId: number) {
      try {
        const data = await accountApi.deleteBoard(boardId)

        if (data.status === 'success') {
          this.boards = this.boards.filter((board) => board.id !== boardId)
          this.closeDeleteModal()
          return { status: 'success' }
        }

        return data
      } catch (error) {
        console.error('Ошибка при удалении доски:', error)
        return { status: 'error', message: 'Ошибка при удалении доски' }
      }
    },

    clearBoards() {
      this.boards = []
      this.isLoaded = false
    },
  },
})
