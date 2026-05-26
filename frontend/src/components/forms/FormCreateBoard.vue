<script setup lang="ts">
import { accountApi } from '@/api/api'
import Input from '@/components/forms/Input.vue'
import IconEye from '@/components/icons/IconEye.vue'
import { visibleOptions } from '@/options/options'
import type { BoardForm } from '@/types/board.types'
import { Field, Form } from 'vee-validate'
import { reactive, ref } from 'vue'
import LoaderCircle from '../ui/LoaderCircle.vue'
import { useBoardStore } from '@/stores/board.stores'

const boardStore = useBoardStore()

const boardForm = reactive<BoardForm>({
  title: '',
  visible_id: 1,
  post_id: boardStore.postAddBoardId,
})

// Validation
const boardValidation = {
  title: (value: string) => {
    if (!value) return 'Введите название доски'
    return true
  },
}

const isLoad = ref(false)

const createBoard = async (values: any, { setErrors }: any) => {
  try {
    isLoad.value = true

    const formData = new FormData()

    // Подготовка данных
    formData.append('title', values.title)
    formData.append('visible_id', values.visible_id)

    if (boardForm.post_id) {
      formData.append('post_id', String(boardForm.post_id))
    }

    const data = await boardStore.createBoard(formData)

    if (data.status === 'success') {
      // Store автоматически добавит доску и закроет модалку
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
  } catch (error) {
    console.error('Ошибка при создании доски:', error)
  } finally {
    isLoad.value = false
  }
}
</script>

<template>
  <Form
    @submit="createBoard"
    :validation-schema="boardValidation"
    :initial-values="boardForm"
    v-slot="{ setErrors }"
    class="flex flex-col gap-6"
  >
    <!-- Title -->
    <Field name="title" v-slot="{ field, errorMessage }">
      <Input
        v-bind="field"
        v-model="field.value"
        type="text"
        :errorMessage="errorMessage"
        label="Название доски"
        placeholder="Smart Casual"
      />
    </Field>

    <!-- Visible -->
    <Field
      name="visible_id"
      v-model="boardForm.visible_id"
      v-slot="{ field, errorMessage }"
    >
      <div class="flex flex-col gap-1">
        <label
          class="flex items-center gap-2 text-(--color-text-second) mb-0.5 group-focus-within:text-indigo-500"
        >
          <IconEye width="20" height="20" />
          <span>Выберите видимость доски</span>
        </label>

        <div class="flex bg-(--color-input) rounded-xl p-1">
          <div
            v-for="resolution in visibleOptions"
            :key="resolution.id"
            class="flex items-center w-full"
          >
            <button
              type="button"
              @click="field.onChange(resolution.id)"
              class="flex flex-col justify-center items-center gap-1.5 w-full h-10 bg-(--color-hover-input) rounded-[10px] cursor-pointer hover:opacity-100 transition-opacity duration-(--speed-transition)"
              :class="
                field.value === resolution.id
                  ? 'bg-(--color-main-panel)!'
                  : 'opacity-60'
              "
            >
              <span>{{ resolution.label }}</span>
            </button>
          </div>
        </div>
      </div>
    </Field>

    <!-- Submit -->
    <button
      type="submit"
      :disabled="isLoad"
      class="w-full text-center bg-(image:--color-brend) hover:bg-(image:--color-hover-brend) text-white py-3 rounded-xl transition-all cursor-pointer disabled:opacity-50"
    >
      Создать
    </button>

    <LoaderCircle v-if="isLoad" />
  </Form>
</template>
