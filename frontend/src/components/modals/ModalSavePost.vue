<script setup lang="ts">
import type { BoardId, BoardPreviewData } from '@/types/board.types'
import IconSuccess from '../icons/IconSuccess.vue'
import IconFavorite from '../icons/IconFavorite.vue'
import IconAddSimple from '../icons/IconAddSimple.vue'
import { useBoardStore } from '@/stores/board.stores'

interface Props {
  boards: BoardPreviewData[]
  postId: number
  isLastColumn: boolean
}

const props = defineProps<Props>()

const boardStore = useBoardStore()
const saveBoardIds = defineModel<BoardId[]>('saveBoardIds')

const emit = defineEmits<{
  (e: 'save-post', boardId: BoardId, postId: number): void
}>()
</script>

<template>
  <div
    class="absolute z-100 top-13 flex flex-col min-w-90 max-w-125 max-h-110 bg-(--color-main-panel) p-3 rounded-[12px] shadow-(--shadow-modal)"
    :class="isLastColumn ? 'right-2' : 'left-2'"
  >
    <div class="overflow-y-scroll scrollbar-none flex flex-col">
      <!-- Избранное -->
      <div
        @click.prevent="emit('save-post', 'favorites', postId)"
        class="btn-fit justify-between! gap-5 font-medium p-3"
      >
        <div class="flex items-center gap-4 w-full">
          <IconFavorite
            stroke-width="1.6"
            class="shrink-0 h-14 p-4 bg-(--color-input) rounded-lg"
          />
          <span class="font-[ComicRelief] text-[16px] truncate"> Избранное </span>
        </div>
        <div
          class="shrink-0 btn-default h-11! border border-(--color-border) shadow-(--shadow-light)"
          :class="
            saveBoardIds?.indexOf('favorites') === -1
              ? 'hover:bg-(--color-input)!'
              : 'bg-black hover:bg-(--color-hover-dark)! text-white'
          "
        >
          <span v-if="saveBoardIds?.indexOf('favorites') === -1"
            >Сохранить</span
          >
          <IconSuccess v-else width="22" stroke-width="1.8" />
        </div>
      </div>

      <!-- User boards -->
      <div
        v-for="board in boards"
        :key="board.id"
        @click.prevent="emit('save-post', board.id, postId)"
        class="btn-fit justify-between! gap-5 font-medium p-3"
      >
        <div class="flex items-center gap-4">
          <div class="h-14 aspect-square bg-(--color-input) rounded-xl">
            <img
              v-if="board.images[0]"
              :src="board.images[0].path_preview"
              :alt="board.title"
              class="shrink-0 img-cover rounded-xl"
            />
          </div>
          <span class="font-[ComicRelief] text-[16px] truncate"> {{ board.title }}</span>
        </div>

        <div
          class="shrink-0 btn-default h-11! border border-(--color-border) shadow-(--shadow-light)"
          :class="
            saveBoardIds?.indexOf(board.id) === -1
              ? 'hover:bg-(--color-input)!'
              : 'bg-black hover:bg-(--color-hover-dark)! text-white'
          "
        >
          <span v-if="saveBoardIds?.indexOf(board.id) === -1">Сохранить</span>
          <IconSuccess v-else width="22" stroke-width="1.8" />
        </div>
      </div>
    </div>

    <!-- Create board -->
    <button
      @click.prevent="boardStore.openCreateModal(postId)"
      class="btn-default min-h-12! border border-(--color-border) font-medium mt-3 shadow-(--shadow-base)"
    >
      <IconAddSimple width="24" stroke-width="1.8" />
      Новая доска
    </button>
  </div>
</template>
