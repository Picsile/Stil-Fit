// Данные для создания поста
export interface PostForm {
  type_id: number
  visible_id: number
  category_id: number
  title: string
  description: string
  tags: string
  links: string[]
}

export interface PostThingForm {
  visible_id: number
  category_id: number
  title: string
  description: string
  tags: string
  links: string[]
}
