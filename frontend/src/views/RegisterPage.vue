<script setup lang="ts">
import { authApi } from '@/api/api'
import router from '@/router'
import { useUserStore } from '@/stores/user.stores'
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { Field } from 'vee-validate'
import type { SubmissionContext } from 'vee-validate'
import InputField from '@/components/forms/InputField.vue'
import InputCheckbox from '@/components/forms/InputCheckbox.vue'
import FormAuth from '@/components/forms/FormAuth.vue'
import type { RegisterForm } from '@/types/auth.types'

const userStore = useUserStore()
const route = useRoute()

const registerForm: RegisterForm = {
  login: '',
  email: '',
  password: '',
  privacy_policy_accepted: false,
  oferta_accepted: true,
}

const registerValidation = {
  login: (value: string) => {
    if (!value) return 'Введите логин'
    if (value.length < 3) return 'Логин должен быть не короче 3 символов'
    return true
  },

  email: (value: string) => {
    if (!value) return 'Введите email'
    return true
  },

  password: (value: string) => {
    if (!value) return 'Введите пароль'
    if (value.length < 8) return 'Пароль должен быть не короче 8 символов'
    return true
  },

  privacy_policy_accepted: (value: boolean) => {
    if (value !== true) return 'Необходимо дать согласие на обработку данных'
    return true
  },

  oferta_accepted: (value: boolean) => {
    if (value !== true) return 'Необходимо принять условия оферты'
    return true
  },
}

const isLoad = ref(false)
const sendEmail = ref('')

async function register(values: any, { setErrors }: SubmissionContext) {
  try {
    isLoad.value = true
    const data = await authApi.register(values)

    if (data.status == 'success') {
      if (data.token) {
        localStorage.setItem('token', data.token)
        userStore.initUser()
        return
      }

      sendEmail.value = values.email
    } else if (data.errorsValidation) {
      const errors = data.errorsValidation

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
    console.error('Ошибка при регистрации: ', e)
  } finally {
    isLoad.value = false
  }
}

watch(
  () => userStore.user?.email_verified,
  (newVal) => {
    if (newVal) router.push({ name: 'home' })
  },
  { immediate: true },
)
</script>

<template>
  <div class="w-screan md:h-screen">
    <RouterLink
      :to="{ name: 'home' }"
      class="absolute z-10 top-4 left-4 md:left-6 text-(--color-text-second) hover:text-(--color-text) cursor-pointer"
    >
      <span>Главная</span>
    </RouterLink>

    <div class="flex w-full h-full">
      <FormAuth
        title="Добро пожаловать в"
        subtitle="Лучшие образы уже ждут тебя!"
        button-text="Зарегистрироваться"
        :is-load="isLoad"
        :validation-schema="registerValidation"
        :initial-values="registerForm"
        @submit="register"
      >
        <template #fields>
          <Field name="login" v-slot="{ field, errorMessage }">
            <InputField
              v-bind="field"
              v-model="field.value"
              type="text"
              :errorMessage="errorMessage"
              label="Логин"
              placeholder="ivan"
            />
          </Field>

          <Field name="email" v-slot="{ field, errorMessage }">
            <InputField
              v-bind="field"
              v-model="field.value"
              type="text"
              :errorMessage="errorMessage"
              label="Адрес электронной почты"
              placeholder="ivan@gmail.com"
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

          <Field
            name="privacy_policy_accepted"
            type="checkbox"
            :value="true"
            v-slot="{ field, errorMessage, handleChange }"
          >
            <InputCheckbox
              :name="field.name"
              :modelValue="field.value"
              :errorMessage="errorMessage"
              @update:modelValue="handleChange"
              @blur="field.onBlur"
            >
              Я даю своё
              <a
                href="/docs/soglasie-stil-fit.pdf"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-500 hover:text-blue-700"
                >Согласие на обработку данных</a
              >
              в соответствии с
              <a
                href="/docs/privacy-policy.pdf"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-500 hover:underline"
              >
                Политикой конфиденциальности
              </a>
            </InputCheckbox>
          </Field>

          <!-- <Field
            name="privacy_accepted"
            type="checkbox"
            v-slot="{ field, errorMessage, handleChange }"
          >
            <InputCheckbox
              :name="field.name"
              v-model="field.value"
              :errorMessage="errorMessage"
              @update:modelValue="handleChange"
              @blur="field.onBlur"
            >
              Я принимаю условия
              <a
                href="/docs/oferta.pdf"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-500 hover:text-blue-700"
                >Публичной оферты</a
              >
            </InputCheckbox>
          </Field> -->
        </template>

        <template #footer>
          <span>Уже есть аккаунт? 👉 </span>
          <RouterLink
            :to="{ name: 'login', query: route.query }"
            class="text-blue-500 hover:text-blue-700"
          >
            Войти
          </RouterLink>
        </template>
      </FormAuth>
    </div>
  </div>
</template>
