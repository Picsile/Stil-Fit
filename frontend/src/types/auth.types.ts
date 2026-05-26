// Данные для регистрации
export interface RegisterForm {
  login: string
  email: string
  password: string
  privacy_policy_accepted: boolean
  oferta_accepted: boolean
}

// Данные для авторизации
export interface LoginForm {
  email: string
  password: string
}

// Данные пользователя
export interface UserData {
  id: number
  login: string
  email: string
  avatar_path: string | null
  background_path: string | null
  role_id: number
  auth_key: string
  email_verified: boolean
  quantity_fitcoins: number
}
