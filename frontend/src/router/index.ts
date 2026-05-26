import BoardPage from '@/views/BoardPage.vue'
import GeneratePage from '@/views/GeneratePage.vue'
import HomePage from '@/views/HomePage.vue'
import LoginPage from '@/views/LoginPage.vue'
import MyProfilePage from '@/views/MyProfilePage.vue'
import ProfilePage from '@/views/ProfilePage.vue'
import PublicPage from '@/views/PublicPage.vue'
import RegisterPage from '@/views/RegisterPage.vue'
import VerifyEmailPage from '@/views/VerifyEmailPage.vue'
import { useUserStore } from '@/stores/user.stores'
import { createRouter, createWebHistory } from 'vue-router'
import BuyCoinPage from '@/views/BuyCoinPage.vue'
import PostPage from '@/views/PostPage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
      meta: { hideNavbar: true },
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
      meta: { hideNavbar: true },
    },
    {
      path: '/verify-email',
      name: 'verify-email',
      component: VerifyEmailPage,
      meta: { hideNavbar: true },
    },
    {
      path: '/public',
      name: 'public',
      component: PublicPage,
      meta: {
        auth: true,
      },
    },
    {
      path: '/post/:postId',
      name: 'post',
      component: PostPage,
    },

    {
      path: '/board/:boardId',
      name: 'board',
      component: BoardPage,
    },
    {
      path: '/generate',
      name: 'generate',
      component: GeneratePage,
    },
    {
      path: '/profile/:userId',
      name: 'profile',
      component: ProfilePage,
    },
    {
      path: '/my-profile',
      name: 'my-profile',
      component: MyProfilePage,
      meta: {
        auth: true,
      },
    },
    {
      path: '/buy-coin',
      name: 'buy-coin',
      component: BuyCoinPage,
      meta: {
        auth: true,
      },
    },
  ],
})

router.beforeEach(async (to, from) => {
  const userStore = useUserStore()
  await userStore.initUser()

  // Перенаправляем на логирование, если требуется
  if (!userStore.isAccount && to.meta.auth) {
    return { name: 'login' }
  }

  // Если пользователь переходит на профиль из своего поста
  if (userStore.isAccount && to.name === 'profile' && userStore.user) {
    if (Number(userStore.user.id) === Number(to.params.userId)) {
      return { name: 'my-profile' }
    }
  }
})

export default router
