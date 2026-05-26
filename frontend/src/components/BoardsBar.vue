<script setup lang="ts">
import BoardListTitle from '@/modules/board/components/BoardsListTitle.vue'
import type { BoardId, BoardTitleData } from '@/types/board.types'

interface Props {
  boards: BoardTitleData[]
  show: boolean
}

defineProps<Props>()

const activeBoardId = defineModel<BoardId>('activeBoardId')
</script>

<template>
  <div
    v-if="show && boards.length"
    class="sticky z-70 top-20 bg-(--color-main-panel) px-4 py-3 transition-all"
  >
  </div>
</template>
