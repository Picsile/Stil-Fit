import { ref, computed } from 'vue'
import type { GeneratedImageData } from '@/types/generation.types'

const selectedImages = ref<GeneratedImageData[]>([])

export function useSelectedImages() {
  const isImageSelected = (imageId: number) => {
    return selectedImages.value.some((img) => img.id === imageId)
  }

  const toggleImage = (image: GeneratedImageData) => {
    const index = selectedImages.value.findIndex((img) => img.id === image.id)

    if (index === -1) {
      selectedImages.value.push(image)
    } else {
      selectedImages.value.splice(index, 1)
    }
  }

  const clearSelection = () => {
    selectedImages.value = []
  }

  const selectedCount = computed(() => selectedImages.value.length)

  return {
    selectedImages,
    selectedCount,
    isImageSelected,
    toggleImage,
    clearSelection,
  }
}
