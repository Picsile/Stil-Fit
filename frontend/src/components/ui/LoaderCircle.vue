<script setup lang="ts">
interface Props {
  width?: string
}

const props = withDefaults(defineProps<Props>(), {
  width: '56px',
})
</script>

<template>
  <div
    class="absolute top-0 left-0 flex justify-center items-center w-full h-full bg-(--color-load)"
  >
    <div
      class="aspect-square border-5 border-(--color-hover-input) border-t-(--color-brend-simple) border-x-(--color-brend-simple) rounded-full animate-spin opacity-60"
      :style="{ width }"
    ></div>
  </div>
</template>
