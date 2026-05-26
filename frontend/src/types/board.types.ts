import type { AuthorPreviewData, ImageData } from './view.types'

// Cистемныe досоки
export type SystemBoard = 'my-posts' | 'top' | 'foryou' | 'favorites' | 'likes'

// ID доски
export type BoardId = SystemBoard | number

// Данные названия доски
export interface BoardTitleData {
  id: BoardId
  title: string
}

// Данные доски
export interface BoardData {
  id: BoardId
  title: string
  visible_id: number
  author: AuthorPreviewData
  created_at: Date
}

// Данные превью доски
export interface BoardPreviewData {
  id: BoardId
  title: string
  visible_id: number
  images: ImageData[] | []
  created_at: Date
}

// Форма создания доски
export interface BoardForm {
  title: string
  visible_id: number
  post_id?: number
}
