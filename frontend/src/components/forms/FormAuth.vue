<script setup lang="ts">
import { Form } from 'vee-validate'
import LoaderCircle from '@/components/ui/LoaderCircle.vue'
import { useUserStore } from '@/stores/user.stores'
import IconBell from '../icons/IconBell.vue'

interface Props {
  title: string
  subtitle: string
  buttonText: string
  isLoad: boolean
  validationSchema?: any
  initialValues?: any
}

defineProps<Props>()

defineEmits<{
  (e: 'submit', values: any, context: any): void
}>()

const userStore = useUserStore()
</script>

<template>
  <!-- Сообщение о подтверждении email -->
  <div
    v-if="userStore.user?.email_verified == false"
    class="flex flex-col items-center max-w-140 p-8 bg-(--color-main-panel) rounded-2xl text-center m-auto"
  >
    <div
      class="flex justify-center items-center w-16 h-16 bg-(--color-brend-light) text-(--color-brend-simple) rounded-2xl"
    >
      <IconBell width="36" />
    </div>
    <h2 class="font-[ComicRelief] font-bold text-xl md:text-2xl mt-5">Подтвердите email</h2>
    <p class="text-[16px] text-(--color-text-second) mt-5">
      Мы отправили письмо с подтверждением на адрес
      <span class="font-medium text-(--color-text)">{{ userStore.user.email }}</span
      >. Перейдите по ссылке в письме, чтобы завершить регистрацию.
    </p>
    <p class="text-[16px] text-(--color-text-second) mt-4">
      Не пришло письмо? Проверьте папку "Спам"
    </p>
    <router-link :to="{ name: 'home' }" class="btn-accent mt-6"> Вернуться на главную </router-link>
  </div>

  <!-- Форма регистрации -->
  <Form
    v-else
    @submit="(values, context) => $emit('submit', values, context)"
    :validation-schema="validationSchema"
    :initial-values="initialValues"
    class="relative flex flex-col items-center gap-8 w-full md:w-130 bg-(--color-main-panel) px-10 md:px-22 py-14 rounded-4xl m-auto mx-2 md:mx-auto"
  >
    <!-- Hi -->
    <div class="flex flex-col gap-2 items-center">
      <img src="/static/logo.svg" width="62" alt="" class="pb-4" />

      <h1 class="flex flex-col items-center gap-2 text-2xl md:text-3xl">
        <span>{{ title }}</span
        ><span class="font-bold">СтильФит</span>
      </h1>
      <span class="text-[14px] md:text-[15px]">{{ subtitle }}</span>
    </div>

    <!-- Fields -->
    <div class="flex flex-col gap-6 w-full">
      <slot name="fields" />
    </div>

    <!-- Btn send -->
    <div class="w-full flex flex-col gap-3 text-center">
      <button
        type="submit"
        class="w-full text-center bg-(image:--color-brend) text-white py-3 rounded-xl hover:bg-(image:--color-hover-brend) transition-all cursor-pointer"
      >
        {{ buttonText }}
      </button>
      <div class="text-[13px] md:text-sm pt-2 pb-4 opacity-90 hover:opacity-100 transition">
        <slot name="footer" />
      </div>
    </div>

    <LoaderCircle v-if="isLoad" />
  </Form>
</template>
