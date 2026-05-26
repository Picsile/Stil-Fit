import { useUserStore } from '@/stores/user.stores'
import { useRouter } from 'vue-router'

export function useAuthRedirect() {
  const userStore = useUserStore()
  const router = useRouter()

  const requireAuth = () => {
    if (userStore.isAccount) {
      return true
    }

    router.push({
      name: 'register',
    })
    return false
  }

  const requireEmailVerified = () => {
    if (userStore.user?.email_verified) {
      return true
    }

    router.push({
      name: 'register',
    })
    return false
  }

  return {
    requireAuth,
    requireEmailVerified,
  }
}
