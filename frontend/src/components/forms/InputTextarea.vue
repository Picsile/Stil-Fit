<script setup lang="ts">
import { useTextareaAutosize } from '@vueuse/core'

interface Props {
  name: string
  type: 'text'
  modelValue?: string
  label: string
  placeholder?: string
  height: number
  errorMessage?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'blur', ev: Event): void
}>()

const { textarea, input } = useTextareaAutosize()

const onInput = (event: Event) => {
  const val = (event.target as HTMLTextAreaElement).value
  input.value = val
  emit('update:modelValue', val)
}

const inputId = 'input-' + Math.floor(Math.random() * 1000)
</script>

<template>
  <div class="group flex flex-col">
    <label class="text-(--color-text-second) mb-1 group-focus-within:text-indigo-500">
      {{ label }}
    </label>

    <textarea
      ref="textarea"
      :id="inputId"
      :name="name"
      :type="type"
      :value="modelValue"
      @input="onInput"
      @blur="emit('blur', $event)"
      :placeholder="placeholder"
      class="w-full bg-(--color-input) hover:bg-(--color-hover-input) focus:bg-(--color-input) px-3.5 py-2.5 rounded-xl focus:outline focus:outline-indigo-500"
      :style="'min-height: ' + height * 4 + 'px'"
    />

    <div v-if="errorMessage" class="text-sm text-red-500! mt-1 -mb-2.5">
      {{ errorMessage }}
    </div>
  </div>
</template>
