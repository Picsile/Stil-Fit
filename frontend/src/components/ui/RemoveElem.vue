<script setup lang="ts">
import Close from './Close.vue'
interface Props {
  width: string
}
const props = defineProps<Props>()
</script>

<template>
  <div
    class="absolute top-1.25 right-1.25 aspect-square! bg-red-100/75 hover:bg-red-100 text-red-500 p-0.5 rounded-lg transition duration-(--speed-transition)"
  >
    <Close :width="width" :height="width" />
  </div>
</template>
