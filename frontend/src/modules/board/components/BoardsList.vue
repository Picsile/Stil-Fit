<script setup lang="ts">
import type { BoardPreviewData } from '@/types/board.types'
import BoardPreview from './BoardPreview.vue'

interface Props {
  boards: BoardPreviewData[]
}

const props = defineProps<Props>()
</script>

<template>
  <div class="flex gap-1 md:gap-4">
    <BoardPreview
      v-for="board in boards"
      :key="board.id"
      :board="board"
      class="last:mr-4"
    />
  </div>
</template>
