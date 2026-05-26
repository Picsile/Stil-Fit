<script setup lang="ts">
interface Props {
  name: string
  type: 'text'
  modelValue?: string

  label: string
  placeholder?: string
  errorMessage?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'blur', ev: Event): void
}>()

const inputId = 'input-' + Math.floor(Math.random() * 1000)
</script>

<template>
  <div class="group flex flex-col">
    <label
      class="label text-(--color-text-second) mb-1 group-focus-within:text-indigo-500"
      >{{ label }}
    </label>

    <input
      :id="inputId"
      :name="name"
      :type="type"
      :value="modelValue"
      @input="
        emit('update:modelValue', ($event.target as HTMLInputElement).value)
      "
      @blur="emit('blur', $event)"
      :placeholder="placeholder"
      class="w-full bg-(--color-input) hover:bg-(--color-hover-input) px-3.5 py-2.5 rounded-xl focus:outline focus:outline-indigo-500"
    />

    <div
      v-if="errorMessage"
      class="errorMessage text-sm text-red-500 mt-1 -mb-2.5"
    >
      <span>{{ errorMessage }}</span>
    </div>
  </div>
</template>
