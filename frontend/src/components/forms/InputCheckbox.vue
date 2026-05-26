<script setup lang="ts">
interface Props {
  name: string
  modelValue?: boolean
  errorMessage?: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'blur', ev: Event): void
}>()

const inputId = 'checkbox-' + Math.floor(Math.random() * 1000)
</script>

<template>
  <div>
    <label :for="inputId" class="flex items-start gap-2 cursor-pointer">
      <input
        :id="inputId"
        type="checkbox"
        :name="name"
        :checked="modelValue"
        @change="
          emit('update:modelValue', ($event.target as HTMLInputElement).checked);
          emit('blur', $event)
        "
        class="mt-1"
      />

      <span class="text-sm">
        <slot />
      </span>
    </label>

    <div v-if="errorMessage" class="text-sm text-red-500 mt-1 -mb-2.5">
      {{ errorMessage }}
    </div>
  </div>
</template>
