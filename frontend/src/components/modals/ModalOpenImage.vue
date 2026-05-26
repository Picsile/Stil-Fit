<script setup lang="ts">
import { useImageStore } from '@/stores/image.stores'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import IconChevronLeft from '@/components/icons/IconChevronLeft.vue'
import IconChevronRight from '@/components/icons/IconChevronRight.vue'
import IconClose from '../icons/iconClose.vue'
import IconDownload from '../icons/IconDownload.vue'
import { viewApi } from '@/api/api'

const imageStore = useImageStore()

const handleKeydown = (e: KeyboardEvent) => {
  if (!imageStore.isModalOpen) return

  if (e.key === 'Escape') {
    imageStore.closeImage()
  } else if (e.key === 'ArrowLeft') {
    imageStore.prevImage()
  } else if (e.key === 'ArrowRight') {
    imageStore.nextImage()
  }
}

// Блокировка скролла при открытии модалки
watch(
  () => imageStore.isModalOpen,
  (isOpen) => {
    if (isOpen) {
      document.documentElement.style.overflow = 'hidden'
      document.body.style.overflow = 'hidden'
    } else {
      document.documentElement.style.overflow = ''
      document.body.style.overflow = ''
    }
  },
)

// Скачивание файла
const isDownloadedFile = ref(false)

const downloadFile = async (path: string) => {
  try {
    isDownloadedFile.value = true

    const data = await viewApi.getFile(path)

    if (data instanceof Blob) {
      const url = window.URL.createObjectURL(data)
      const link = document.createElement('a')
      link.href = url

      const pathParts = path.split('/')
      const fileName = pathParts[pathParts.length - 1] ?? 'image'
      link.setAttribute('download', fileName)

      document.body.appendChild(link)
      link.click()
    } else {
      console.warn('Получены данные не в формате Blob:', data)
    }
  } catch (e) {
    console.error('Ошибка при скачивании файла: ', e)
  } finally {
    isDownloadedFile.value = false
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.documentElement.style.overflow = ''
  document.body.style.overflow = ''
})
</script>

<template>
  <Transition name="modal">
    <div
      v-if="imageStore.isModalOpen"
      @click="imageStore.closeImage()"
      class="fixed z-300 inset-0 flex w-screen h-screen bg-(--color-dark)/90 cursor-pointer"
    >
      <!-- Изображение -->
      <div @click="imageStore.closeImage()" class="overflow-y-auto flex w-full h-full">
        <div @click.stop="" class="flex flex-col md:max-w-[70%] p-4 m-auto">
          <img :src="imageStore.currentImageUrl" alt="" />
        </div>
      </div>

      <div class="flex gap-2 absolute top-2 md:top-6 right-2 md:right-10">
        <button
          @click.stop="() => downloadFile(imageStore.currentImageUrl)"
          class="flex justify-center items-center h-auto aspect-square bg-transparent! hover:bg-(--color-dark)/80! p-3 rounded-xl text-white transition cursor-pointer"
          :class="isDownloadedFile ? 'disabled' : ''"
        >
          <IconDownload class="w-7 h-7" stroke-width="1.2" />
        </button>

        <button
          @click="imageStore.closeImage()"
          class="flex justify-center items-center h-auto aspect-square bg-transparent! hover:bg-(--color-dark)/80! p-2 rounded-xl text-white transition cursor-pointer"
        >
          <IconClose class="w-9 h-9" stroke-width="1.2" />
        </button>
      </div>

      <!-- Предыдущее -->
      <button
        v-if="imageStore.currentIndex > 0"
        @click.stop="imageStore.prevImage()"
        class="flex justify-center items-center absolute top-1/2 left-6 md:left-10 h-auto bg-transparent! hover:bg-transparent! md:hover:bg-(--color-dark)/80! active:bg-(--color-dark)/80! p-2 rounded-xl text-white -translate-y-1/2 duration-400"
      >
        <IconChevronLeft class="w-9 h-9" />
      </button>

      <!-- Следующее -->
      <button
        v-if="imageStore.currentIndex < imageStore.images.length - 1"
        @click.stop="imageStore.nextImage()"
        class="flex justify-center items-center absolute top-1/2 right-6 md:right-10 h-auto bg-transparent! hover:bg-transparent! md:hover:bg-(--color-dark)/80! active:bg-(--color-dark)/80! p-2 rounded-xl text-white -translate-y-1/2 duration-400"
      >
        <IconChevronRight class="w-9 h-9" />
      </button>
    </div>
  </Transition>
</template>
