<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authApi } from '@/api/api'
import LoaderCat from '@/components/ui/LoaderCat.vue'
import IconSuccess from '@/components/icons/IconSuccess.vue'
import IconError from '@/components/icons/IconError.vue'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const isSuccess = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  const token = route.query.token as string

  if (!token) {
    errorMessage.value = 'Токен подтверждения не указан'
    isLoading.value = false
    return
  }

  try {
    const data = await authApi.verifyEmail(token)

    if (data.status === 'success') {
      isSuccess.value = true
      // setTimeout(() => {
      //   router.push({ name: '/' })
      // }, 3000)
    } else {
      errorMessage.value = data.message || 'Ошибка при подтверждении email'
    }
  } catch (error) {
    console.error('Ошибка при подтверждении email:', error)
    errorMessage.value = 'Произошла ошибка при подтверждении email'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="w-screen h-screen">
    <RouterLink
      :to="{ name: 'home' }"
      class="absolute z-10 top-4 left-4 md:left-6 text-(--color-text-second) hover:text-(--color-text) cursor-pointer"
    >
      <span>Главная</span>
    </RouterLink>

    <div class="flex w-full h-full">
      <!-- Загрузка -->
      <div v-if="isLoading" class="flex flex-col items-center gap-4">
        <LoaderCat />
        <p class="text-lg">Подтверждение email...</p>
      </div>

      <!-- Успех -->
      <div
        v-if="isSuccess"
        class="flex flex-col items-center max-w-140 p-8 bg-(--color-main-panel) rounded-2xl text-center m-auto"
      >
        <div
          class="flex justify-center items-center w-16 h-16 bg-emerald-100 text-emerald-400 rounded-2xl"
        >
          <IconSuccess width="36" />
        </div>
        <h2 class="font-[ComicRelief] font-bold text-2xl mt-5">Email подтвержден!</h2>
        <p class="text-[16px] text-(--color-text-second) mt-5">
          Ваш email успешно подтвержден. Сейчас вы будете перенаправлены на страницу входа.
        </p>
        <router-link :to="{ name: 'home' }" class="btn-accent mt-6">
          Перейти на главную
        </router-link>
      </div>

      <!-- Ошибка -->
      <div
        v-else
        class="flex flex-col items-center max-w-140 p-8 bg-(--color-main-panel) rounded-2xl text-center m-auto"
      >
        <div
          class="flex justify-center items-center w-16 h-16 bg-red-100 text-red-400 rounded-2xl"
        >
          <IconError width="36" />
        </div>
        <h2 class="font-[ComicRelief] font-bold text-2xl mt-5">Ошибка подтверждения</h2>
        <p class="font-medium text-(--color-text)">{{ errorMessage }}</p>
        <router-link :to="{ name: 'home' }" class="btn-accent mt-6">
          Перейти на главную
        </router-link>
      </div>
    </div>
  </div>
</template>
