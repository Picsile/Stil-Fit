// Данные поста
export interface PostData {
  id: number
  type_id: number
  visible: number
  images: ImageData[]
  title: string
  description: string
  tags: TagData[]
  links: LinkData[]
  items: PostPreviewData[]
  likes_count: number
  created_at: string
  author: AuthorPreviewData
  is_liked: boolean
  is_saved: boolean
}

// Данные превью поста
export interface PostPreviewData {
  id: number
  title: string
  description: string
  main_image: ImageData
  author: AuthorPreviewData
  likes_count: number
  is_liked: boolean
  is_saved: boolean
  saved_board_ids: number[]
}

// Данные изображения
export interface ImageData {
  id: number
  path?: string
  path_preview?: string
  width: number
  height: number
}

// Данные тега
export interface TagData {
  id: number
  title: string
}

// Данные ссылки
export interface LinkData {
  id: number
  url: string
}

// Данные автора
export interface AuthorPreviewData {
  id: number
  login: string
  avatar_path: string | null
}

// Данные профиля
export interface ProfileData {
  id: number
  login: string
  avatar_path: string | null
  background_path: string | null
  posts_count: number
  likes_count: number
}
