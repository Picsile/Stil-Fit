<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import type { BoardId, BoardTitleData } from '@/types/board.types'
import BoardTitle from './BoardTitle.vue'

interface Props {
  boards: BoardTitleData[]
}

const props = defineProps<Props>()

const activeBoardId = defineModel<BoardId>('active-id', { default: 'top' })

const itemRefs = ref<any[]>([])

const borderStyles = ref({
  left: '0px',
  width: '0px',
})

const calcStyle = async () => {
  const index = props.boards.findIndex(
    (board) => board.id === activeBoardId.value,
  )

  if (index === -1 || !itemRefs.value[index]) return

  const boardElem = itemRefs.value[index].$el

  borderStyles.value = {
    left: `${boardElem.offsetLeft}px`,
    width: `${boardElem.offsetWidth}px`,
  }
}

// Start
onMounted(() => {
  calcStyle()
})

watch(activeBoardId, () => {
  setInterval(calcStyle, 20)
})
</script>

<template>
  <div class="overflow-x-scroll scrollbar-none relative flex gap-5 pb-0.5">
    <BoardTitle
      v-for="board in boards"
      :key="board.id"
      :boardTitle="board.title"
      @click="activeBoardId = board.id"
      ref="itemRefs"
      :class="{ 'text-(--color-text-second)': !(activeBoardId === board.id) }"
    />
    <div
      class="absolute bottom-0 border transition-all ease-in-out"
      :style="borderStyles"
    ></div>
  </div>
</template>
