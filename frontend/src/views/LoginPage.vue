<script setup lang="ts">
import { authApi } from '@/api/api'
import router from '@/router'
import { useUserStore } from '@/stores/user.stores'
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { Field } from 'vee-validate'
import type { SubmissionContext } from 'vee-validate'
import InputField from '@/components/forms/InputField.vue'
import FormAuth from '@/components/forms/FormAuth.vue'
import type { LoginForm } from '@/types/auth.types'

const userStore = useUserStore()
const route = useRoute()

const loginData: LoginForm = {
  email: '',
  password: '',
}

const loginValidation = {
  email: (value: string) => {
    if (!value) return 'Введите email'
    return true
  },

  password: (value: string) => {
    if (!value) return 'Введите пароль'
    return true
  },
}

const isLoad = ref<boolean>(false)

async function login(values: any, { setErrors }: SubmissionContext) {
  try {
    isLoad.value = true
    const data = await authApi.login(values)

    if (data.status === 'success') {
      localStorage.setItem('token', data.token)
      userStore.initUser()
      return
    } else if (data.errorsValidation) {
      const errors = data.errorsValidation

      console.log(data.errorsValidation)

      for (const key in errors) {
        if (errors.hasOwnProperty(key)) {
          errors[key] = errors[key][0]
        }
      }
      setErrors(data.errorsValidation)
    } else {
      throw new Error(data?.message)
    }
  } catch (e) {
    console.error('Ошибка при авторизации: ', e)
  } finally {
    isLoad.value = false
  }
}

watch(
  () => userStore.isAccount,
  (newVal) => {
    if (newVal && userStore.user?.email_verified) router.push({ name: 'home' })
  },
  { immediate: true },
)
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
      <FormAuth
        title="С возвращением в"
        subtitle="Лучшие образы уже ждут тебя!"
        button-text="Войти"
        :is-load="isLoad"
        :validation-schema="loginValidation"
        :initial-values="loginData"
        @submit="login"
      >
        <template #fields>
          <Field name="email" v-slot="{ field, errorMessage }">
            <InputField
              v-bind="field"
              v-model="field.value"
              type="text"
              :errorMessage="errorMessage"
              label="Адрес электронной почты"
              placeholder="lookfit@gmail.com"
            />
          </Field>

          <Field name="password" v-slot="{ field, errorMessage }">
            <InputField
              v-bind="field"
              v-model="field.value"
              type="text"
              :errorMessage="errorMessage"
              label="Пароль"
              placeholder=""
            />
          </Field>
        </template>

        <template #footer>
          <span>Нет аккаунта? 👉 </span>
          <RouterLink
            :to="{ name: 'register', query: route.query }"
            class="text-blue-500 hover:text-blue-700"
          >
            Зарегистрироваться
          </RouterLink>
        </template>
      </FormAuth>
    </div>
  </div>
</template>
