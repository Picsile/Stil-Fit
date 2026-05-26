<script setup lang="ts">
import type { BoardPreviewData } from '@/types/board.types'

interface Props {
  board: BoardPreviewData
}

const props = defineProps<Props>()
</script>

<template>
  <router-link
    :to="{ name: 'board', params: { boardId: board.id } }"
    class="flex flex-col gap-1.5 hover:brightness-75"
  >
    <div class="overflow-hidden flex gap-0.5 w-64 aspect-3/2 rounded-2xl">
      <img
        v-if="board.images.length"
        :src="board.images[0]?.path_preview"
        :alt="board.title"
        class="shrink-0 overflow-hidden object-cover h-full bg-(--color-input)"
        :class="board.images.length > 1 ? 'aspect-square ' : 'w-full'"
      />
      <div v-else class="w-full h-full bg-(--color-input)"></div>

      <div class="flex flex-col gap-0.5">
        <img
          v-if="board.images[1]"
          :src="board.images[1]?.path_preview"
          :alt="board.title"
          class="shrink-0 object-cover flex-1 aspect-square bg-(--color-input)"
        />

        <img
          v-if="board.images[2]"
          :src="board.images[2]?.path_preview"
          :alt="board.title"
          class="shrink-0 object-cover flex-1 aspect-square bg-(--color-input)"
        />
      </div>
    </div>

    <span class="font-[ComicRelief] text-[16px] opacity-75">{{ board.title }}</span>
  </router-link>
</template>
