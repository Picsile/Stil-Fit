import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { GeneratedImageData } from '@/types/generation.types'
import type { PostPreviewData } from '@/types/view.types'

export const useGenerationDataStore = defineStore('generationData', () => {
  const selectedImages = ref<GeneratedImageData[]>([])
  const generationPrompt = ref<string>('')

  // Проверка, выбрано ли изображение
  const isImageSelected = (imageId: number) => {
    return selectedImages.value.some((img) => img.id === imageId)
  }

  // Добавить/удалить изображение
  const toggleImage = (image: GeneratedImageData, prompt: string) => {
    const index = selectedImages.value.findIndex((img) => img.id === image.id)

    if (index === -1) {
      // Добавляем изображение с его items
      selectedImages.value.push(image)
      // Сохраняем промпт из самой свежей генерации
      generationPrompt.value = prompt
    } else {
      // Удаляем изображение
      selectedImages.value.splice(index, 1)
      // Если остались изображения, берем промпт из последнего
      if (selectedImages.value.length > 0) {
        // Промпт остается от последней добавленной генерации
      } else {
        // Если изображений не осталось, очищаем промпт
        generationPrompt.value = ''
      }
    }
  }

  // Получить все уникальные items из всех выбранных изображений
  const getAllItems = computed<PostPreviewData[]>(() => {
    const itemsMap = new Map<number, PostPreviewData>()

    selectedImages.value.forEach((image) => {
      if (image.generationItems) {
        image.generationItems.forEach((item) => {
          if (!itemsMap.has(item.id)) {
            itemsMap.set(item.id, item)
          }
        })
      }
    })

    return Array.from(itemsMap.values())
  })

  // Получить все пути изображений
  const getImagePaths = computed<string[]>(() => {
    return selectedImages.value.map((img) => img.path)
  })

  // Количество выбранных изображений
  const selectedCount = computed(() => selectedImages.value.length)

  // Очистить выбор
  const clearSelection = () => {
    selectedImages.value = []
    generationPrompt.value = ''
  }

  return {
    selectedImages,
    selectedCount,
    generationPrompt,
    isImageSelected,
    toggleImage,
    getAllItems,
    getImagePaths,
    clearSelection,
  }
})
