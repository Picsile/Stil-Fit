import type { BoardId } from '@/types/board.types'

// Доски
export const systemBoards = [
  { id: 'top' as BoardId, title: 'Топ' },
  { id: 'foryou' as BoardId, title: 'Для вас' },
]

export const boardsForCreate = [
  { id: 'top' as BoardId, title: 'Топ' },
  { id: 'my-posts' as BoardId, title: 'Мои' },
  { id: 'likes' as BoardId, title: 'Лайки' },
  { id: 'favorites' as BoardId, title: 'Избранное' },
]

export const boardsForProfile = [
  { id: 'my-posts' as BoardId, title: 'Созданные' },
  { id: 'likes' as BoardId, title: 'Лайки' },
  { id: 'favorites' as BoardId, title: 'Избранное' },
]

export const boardsForMyProfile = [
  { id: 'my-posts' as BoardId, title: 'Мои' },
  { id: 'likes' as BoardId, title: 'Лайки' },
  { id: 'favorites' as BoardId, title: 'Избранное' },
]

// Генерация
export const typeOptions = [
  { id: 1, title: 'Thing', label: 'Вещь' },
  { id: 2, title: 'Outfit', label: 'Образ' },
  { id: 3, title: 'Other', label: 'Другое' },
] as const

export const visibleOptions = [
  { id: 1, title: 'Public', label: 'Публичный' },
  { id: 2, title: 'Private', label: 'Приватный' },
] as const

export const ratioOptions = [
  { id: 1, value: '16:9', frameSize: 'min-w-6.5 w-6.5 min-h-4.5 h-4.5' },
  { id: 2, value: '4:3', frameSize: 'min-w-5.5 min-h-4.5 w-5.5 h-4.5' },
  { id: 3, value: '1:1', frameSize: 'min-w-4.5 min-h-4.5 w-4.5 h-4.5' },
  { id: 4, value: '3:4', frameSize: 'min-w-4.5 min-h-5.5 w-4.5 h-5.5' },
  { id: 5, value: '9:16', frameSize: 'min-w-4.5 min-h-6.5 w-4.5 h-6.5' },
] as const

export const resolutionOptions = [
  { id: 1, value: '2K' },
  { id: 2, value: '4K' },
] as const
