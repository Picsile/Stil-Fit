<script setup lang="ts">
import Close from '../ui/Close.vue'
import FormCreateBoard from '../forms/FormCreateBoard.vue'
import IconBoard from '@/components/icons/IconBoard.vue'
import { useBoardStore } from '@/stores/board.stores'

const boardStore = useBoardStore()
</script>

<template>
  <Transition name="modal">
    <div
      v-if="boardStore.isCreateModalOpen"
      @click="boardStore.closeCreateModal()"
      ref="modalCreateBoard"
      class="fixed z-100 top-0 left-0 flex justify-center items-center w-screen h-screen bg-slate-900/50 p-4"
    >
      <div
        @click.stop
        class="overflow-hidden relative flex flex-col gap-6 w-full max-w-120 bg-(--color-main-panel) p-8 rounded-3xl"
      >
        <!-- Header -->
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <IconBoard width="34" stroke-width="1.1" />
            <h3 class="text-lg">Создание новой доски</h3>
          </div>
          <Close @click="boardStore.closeCreateModal()" class="w-7" />
        </div>

        <!-- Form -->
        <FormCreateBoard />
      </div>
    </div>
  </Transition>
</template>
