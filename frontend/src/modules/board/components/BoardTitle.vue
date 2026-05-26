<script setup lang="ts">
import type { BoardId } from '@/types/board.types'

interface Props {
  boardTitle: string
}

const props = defineProps<Props>()
</script>

<template>
  <span
    class="text-[16px] font-[ComicRelief] px-0.5 whitespace-nowrap tracking-wider cursor-pointer"
  >
    {{ boardTitle }}
  </span>
</template>
