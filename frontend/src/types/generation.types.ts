import type { PostPreviewData } from './view.types'

// Данные для генерации
export interface GenerationForm {
  type_id: number
  visible_id: number
  prompt: string
  ratio_id: number
  resolution_id: number
  quantity: number
}

// Данные генерации
export interface GenerationData {
  id: number | null
  type: 'Thing' | 'Outfit'
  visible_id: number
  prompt: string
  ratio: string
  resolution: string
  quantity: number
  created_at: Date | string
  appearances?: File[]
  items: PostPreviewData[]
  generated_images?: GeneratedImageData[]
  status: 'Generating' | 'Completed' | 'Error'
  error?: string
}

// Данные сгенерированного изображения
export interface GeneratedImageData {
  id: number
  path: string
  width: number
  height: number
  generationItems?: PostPreviewData[]
}
