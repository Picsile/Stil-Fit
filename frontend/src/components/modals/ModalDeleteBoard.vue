<script setup lang="ts">
import Close from '../ui/Close.vue'
import IconTrash from '@/components/icons/IconTrash.vue'
import { useBoardStore } from '@/stores/board.stores'
import { ref } from 'vue'

const boardStore = useBoardStore()

const emit = defineEmits<{
  confirm: []
}>()

const isDeleting = ref(false)

const handleDelete = async () => {
  isDeleting.value = true
  emit('confirm')
}
</script>

<template>
  <Transition name="modal">
    <div
      v-if="boardStore.isDeleteModalOpen"
      @click="boardStore.closeDeleteModal()"
      class="fixed z-100 top-0 left-0 flex justify-center items-center w-screen h-screen bg-slate-900/50 p-4"
    >
      <div
        @click.stop
        class="overflow-hidden relative flex flex-col items-center text-center w-full max-w-120 bg-(--color-main-panel) p-8 rounded-3xl"
      >
        <div class="absolute top-6 right-6 flex justify-between items-center">
          <Close @click="boardStore.closeDeleteModal()" class="w-7" />
        </div>

        <div
          class="p-4 rounded-4xl bg-(--color-brend-light) text-(--color-brend-simple)"
        >
          <IconTrash width="34" />
        </div>

        <h3 class="text-xl mt-3">Удаление доски</h3>

        <p class="text-(--color-text-second) mt-3">
          Вы уверены, что хотите удалить эту доску? Это действие нельзя будет
          отменить.
        </p>

        <div class="flex gap-2 w-full mt-6">
          <button
            @click="handleDelete()"
            class="btn-default w-full bg-(--color-input) hover:bg-red-500! text-(--color-text-second) hover:text-white"
            :disabled="isDeleting"
          >
            Удалить
          </button>
          <button
            @click="boardStore.closeDeleteModal()"
            class="btn-accent w-full"
            :disabled="isDeleting"
          >
            Отмена
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
