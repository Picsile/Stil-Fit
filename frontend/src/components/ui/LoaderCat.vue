<script setup lang="ts">
interface Props {
  width?: string
}

const props = withDefaults(defineProps<Props>(), {
  width: '160px',
})
</script>

<template>
  <Transition name="loader-cat" class="transition">
    <div
      class="loader absolute z-40 top-0 left-0 flex justify-center items-center w-full h-full bg-(--color-load)"
    >
      <img src="/static/loaderCat.gif" :width="width" alt="Кот штык" />
    </div>
  </Transition>
</template>

<style scoped>
.loader-cat-enter-active,
.loader-cat-leave-active {
  transition:
    opacity 0.35s ease,
    transform 0.35s ease;
}

.loader-cat-enter-from {
  opacity: 0;
  transform: scale(1);
}

.loader-cat-enter-to {
  opacity: 1;
  transform: scale(1);
}

.loader-cat-leave-from {
  opacity: 1;
  transform: scale(1);
}

.loader-cat-leave-to {
  opacity: 0;
  transform: scale(1.1);
}
</style>
