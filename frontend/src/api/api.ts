import type { BoardId } from '@/types/board.types'
import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE_URL

// Устанавливаем базовый URL сервера
const api = axios.create({
  baseURL: baseURL || '/backend',
})

api.interceptors.request.use((config) => {
  // Подставляем токен
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  // Выставляем тип контента
  if (!('Content-Type' in config.headers)) {
    config.headers['Content-Type'] = 'application/json'
  }

  return config
})

// Auth API
export const authApi = {
  // Регистрация
  register: async (formData: any) => (await api.post('/auth/register', formData)).data,

  // Авторизация
  login: async (formData: any) => (await api.post('/auth/login', formData)).data,

  // Аунтификация по токену
  findByToken: async (token: string) =>
    (
      await api.get('/auth/find-by-token', {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
    ).data,

  // Выход
  logout: async () => {
    await api.get('/auth/logout')
    return localStorage.removeItem('token')
  },

  // Подтверждение email
  verifyEmail: async (token: string) =>
    (await api.get('/auth/verify-email', { params: { token } })).data,
}

// View API
export const viewApi = {
  // Получение поста
  getPost: async (postId: number) => (await api.get(`/view/post`, { params: { postId } })).data,

  // Получение постов из системных досок или досок пользователя
  getPostsSystemBoard: async (boardId: string, offset: number, limit: number, userId?: number) =>
    (
      await api.get('/view/posts-system-board', {
        params: { boardId, offset, limit, userId },
      })
    ).data,

  // Получение постов для досок
  getPostsForBoard: async (boardId: number, limit: number) =>
    (
      await api.get(`/view/posts-for-board`, {
        params: { boardId, limit },
      })
    ).data,

  // Получение постов из досок
  getPostsInBoard: async (boardId: number, offset: number, limit: number) =>
    (
      await api.get(`/view/posts-in-board`, {
        params: { boardId, offset, limit },
      })
    ).data,

  // Получение постов по запросу
  searchPosts: async (query: string, offset: number, limit: number) =>
    (await api.get('/view/search-posts', { params: { query, offset, limit } })).data,

  // Получение похожих постов
  getSimilar: async (postId: number, offset: number, limit: number) =>
    (await api.get(`/view/similar`, { params: { postId, offset, limit } })).data,

  // Получение постов по массиву ID
  getPostsByIds: async (ids: number[]) => (await api.post('/view/posts-by-ids', { ids })).data,

  // Получение образов, содержащих конретную вешь
  getOutfitsWithItem: async (itemId: number, offset: number, limit: number) =>
    (
      await api.get(`/view/outfits-with-item`, {
        params: { itemId, offset, limit },
      })
    ).data,

  // Получение доски
  getBoard: async (boardId: number) => (await api.get(`/view/board`, { params: { boardId } })).data,

  // Получение досок по id пользователя
  getBoards: async (userId: number) => (await api.get(`/view/boards`, { params: { userId } })).data,

  // Получение профиля пользователя
  getProfile: async (userId: number) =>
    (await api.get(`/view/profile`, { params: { userId } })).data,

  // Получение тегов по запросу
  getTags: async (query: string) => (await api.get('/view/tags', { params: { query } })).data,

  // Скачивание файла по пути
  getFile: async (url: string) =>
    (
      await api.get('/view/file', {
        params: { url },
        responseType: 'blob',
      })
    ).data,
}

// Post API
export const postApi = {
  // Создать пост
  createPost: async (formData: FormData) => {
    return (
      await api.post('/post/create-post', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    ).data
  },

  // Удалить пост
  deletePost: async (postId: number) => (await api.post(`/post/delete-post`, { postId })).data,
}

// Generation API
export const generationApi = {
  // Получение генераций
  getGenerations: async (offset: number, limit = 5) =>
    (
      await api.get(`/view/generations`, {
        params: { offset, limit },
      })
    ).data,

  // Создание генерации
  createGeneration: async (formData: FormData) => {
    return (
      await api.post('/generation/create-generation', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    ).data
  },

  // Проверка статуса генерации
  checkGeneration: async (generationId: number) => {
    return (await api.get(`/generation/check-generation/${generationId}`)).data
  },

  // Старый метод (оставлен для совместимости)
  generateOutfit: async (formData: FormData) => {
    return (
      await api.post('/generation/generate-outfit', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    ).data
  },
}

// Account API
export const accountApi = {
  // Поставить/убрать лайк
  like: async (postId: number) => (await api.post('/account/like', { postId })).data,

  // Создание доски
  createBoard: async (formData: FormData) =>
    (await api.post('/account/create-board', formData)).data,

  // Удаление доски
  deleteBoard: async (boardId: number) =>
    (await api.delete('/account/delete-board', { data: { boardId } })).data,

  // Удаление поста
  deletePost: async (postId: number, reason = 'Удалено автором') =>
    (await api.delete('/account/delete-post', { data: { postId, reason } })).data,

  // Сохранение поста в доску
  savePost: async (boardId: BoardId, postId: number) =>
    (
      await api.post('/account/save-post', {
        boardId,
        postId,
      })
    ).data,

  // Обновление аватарки
  uploadAvatar: async (formData: FormData) =>
    (
      await api.post('/account/update-avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    ).data,

  // Обновление фонового изображения
  uploadBgImage: async (formData: FormData) =>
    (
      await api.post('/account/update-bg-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
    ).data,
}

export default api
