<script setup lang="ts">
import { useRouter } from 'vue-router'
import IconArrowBack from '../icons/IconArrowBack.vue'

const router = useRouter()
</script>

<template>
  <button
    type="button"
    @click="router.back()"
    class="btn-default min-w-auto! px-2.5!"
  >
    <IconArrowBack width="24" class="transition" />
  </button>
</template>

<style scoped>

.btn-default:hover svg {
  transform: translateX(-2px);
}
</style>
