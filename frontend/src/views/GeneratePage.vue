<script setup lang="ts">
import { generationApi } from '@/api/api'
import Generation from '@/modules/generation/components/Generation.vue'
import Header from '@/components/Header.vue'
import IconInBox from '@/components/icons/IconInBox.vue'
import LoaderCat from '@/components/ui/LoaderCat.vue'
import Navbar from '@/components/Navbar.vue'
import type { GenerationData } from '@/types/generation.types'
import { useInfiniteScroll, useMediaQuery } from '@vueuse/core'
import { computed, ref, onMounted, watch } from 'vue'
import FormGeneration from '@/components/forms/FormGeneration.vue'
import GenerationLayout from '@/layouts/GenerationLayout.vue'
import { useGenerationDataStore } from '@/stores/generationData.stores'
import { useIsMobile } from '@/composables/useIsMobile'

const generationDataStore = useGenerationDataStore()
const { isMobile } = useIsMobile()

const sanitizeError = (msg?: string) => {
  if (!msg || /APIMart|Не удалось загрузить изображение/i.test(msg)) {
    return 'Проблема с соединением, попробуйте позже'
  }
  return msg
}

const widthPanel = ref(30)
const isLg = useMediaQuery('(min-width: 1024px)')

const headerLeftStyle = computed(() => ({
  left: isMobile.value
    ? '0'
    : isLg.value
      ? `calc(var(--w-navbar) + ${widthPanel.value}%)`
      : 'var(--w-navbar)',
}))

// Generations
const generations = ref<GenerationData[]>([])
const activePolls = new Set<number>()
const offset = ref<number>(0)
const limit = 5
const hasMore = ref<boolean>(true)

const isLoadGenerations = ref<boolean>(false)

const getGenerations = async () => {
  try {
    if (!hasMore.value) return

    isLoadGenerations.value = true

    const data = await generationApi.getGenerations(offset.value, limit)

    console.log(data)

    if (data.status === 'success') {
      generations.value.push(...data.generations)

      if (data.generations.length < limit) {
        hasMore.value = false
      } else {
        offset.value += limit
      }
    } else {
      hasMore.value = false
    }
  } catch (e) {
    console.error('Ошибка при получении генераций: ', e)
    hasMore.value = false
  } finally {
    isLoadGenerations.value = false
  }
}

const updateGeneration = (newGeneration: GenerationData) => {
  if (newGeneration.id === null) {
    const skeletonIndex = generations.value.findIndex((gen) => gen.id === null)

    if (skeletonIndex === -1) {
      generations.value.unshift(newGeneration)
    } else {
      generations.value[skeletonIndex] = { ...generations.value[skeletonIndex], ...newGeneration }
    }
    return
  }

  const skeletonIndex = generations.value.findIndex((gen) => gen.id === null)

  if (skeletonIndex !== -1) {
    generations.value[skeletonIndex] = { ...generations.value[skeletonIndex], ...newGeneration }
  } else {
    const index = generations.value.findIndex((gen) => gen.id === newGeneration.id)

    if (index === -1) {
      generations.value.unshift(newGeneration)
    } else {
      const existingGeneration = generations.value[index]

      if (existingGeneration) {
        if (newGeneration.generated_images) {
          const existingImages = existingGeneration.generated_images || []
          const existingImageIds = new Set(existingImages.map((img) => img.id))

          const newImages = newGeneration.generated_images.filter(
            (img) => !existingImageIds.has(img.id),
          )

          newGeneration.generated_images = [...existingImages, ...newImages]
        }
        generations.value[index] = { ...existingGeneration, ...newGeneration }
      }
    }
  }

  saveActiveGenerations()
}

useInfiniteScroll(
  window,
  () => {
    if (!isLoadGenerations.value && hasMore.value) {
      getGenerations()
    }
  },
  { distance: 400 },
)

const removeGeneration = (id: number | null) => {
  if (id === null) {
    generations.value = generations.value.filter((gen) => gen.id !== null)
  } else {
    generations.value = generations.value.filter((gen) => gen.id !== id)
  }
  saveActiveGenerations()
}

const saveActiveGenerations = () => {
  const activeIds = generations.value
    .filter((gen) => gen.status === 'Generating' && gen.id !== null)
    .map((gen) => gen.id)
  localStorage.setItem('active-generations', JSON.stringify(activeIds))
}

const startPollingForGeneration = (generationId: number) => {
  if (activePolls.has(generationId)) {
    return
  }

  activePolls.add(generationId)

  const maxAttempts = 100
  let attempts = 0

  const poll = async () => {
    if (!activePolls.has(generationId)) {
      return
    }

    if (attempts >= maxAttempts) {
      updateGeneration({
        id: generationId,
        status: 'Error',
        error: 'Превышено время ожидания генерации',
      } as GenerationData)
      activePolls.delete(generationId)
      saveActiveGenerations()
      return
    }

    try {
      const data = await generationApi.checkGeneration(generationId)

      if (data.status === 'success') {
        updateGeneration({
          id: generationId,
          generated_images: data.generation.images,
          status: data.generation.status,
        } as GenerationData)

        if (data.generation.status === 'Completed') {
          activePolls.delete(generationId)
          saveActiveGenerations()
          return
        }
      } else if (data.status === 'error') {
        updateGeneration({
          id: generationId,
          status: 'Error',
          error: sanitizeError(data.message),
        } as GenerationData)
        activePolls.delete(generationId)
        saveActiveGenerations()
        return
      } else if (data.status === 'processing') {
        updateGeneration({
          id: generationId,
          generated_images: data.generation.images,
          status: 'Generating',
        } as GenerationData)
        attempts++
        setTimeout(poll, 3000)
      }
    } catch (error) {
      console.error('Ошибка при проверке статуса генерации:', error)
      attempts++
      setTimeout(poll, 3000)
    }
  }

  poll()
}

watch(
  generations,
  (newGenerations) => {
    newGenerations.forEach((generation) => {
      if (
        generation.status === 'Generating' &&
        generation.id !== null &&
        !activePolls.has(generation.id)
      ) {
        startPollingForGeneration(generation.id)
      }
    })
  },
  { deep: true },
)

// Start
onMounted(async () => {
  await getGenerations()

  // Запускаем polling для незавершенных генераций
  generations.value.forEach((generation) => {
    if (generation.status === 'Generating' && generation.id !== null) {
      startPollingForGeneration(generation.id)
    }
  })
})
</script>

<template>
  <GenerationLayout v-model="widthPanel">
    <template #panel>
      <FormGeneration @update-generation="updateGeneration" @remove-generation="removeGeneration" />
    </template>

    <template #default>
      <Header :show-selection-panel="true" :hide-boards="true" :style="headerLeftStyle" />

      <Generation
        v-for="(generationData, index) in generations"
        :key="generationData.id ?? `skeleton-${index}`"
        :generationData="generationData"
        @getGenerations="getGenerations"
      />

      <div class="relative flex flex-1 justify-center items-center max-h-[calc(100vh-8rem)]">
        <div
          v-if="!isLoadGenerations && generations.length === 0"
          class="flex flex-col items-center gap-2 text-center w-100 opacity-90"
        >
          <IconInBox class="w-18 h-18" stroke-width="0.7" />
          <span class="text-xl">Тут пусто</span>
          <span class="text-base"
            >Попробуйте
            <span class="text-blue-500 font-medium">сгенерировать</span>
            новые изображения с использованием наших ресурсов</span
          >
        </div>

        <LoaderCat v-if="isLoadGenerations" width="170px" />
      </div>
    </template>
  </GenerationLayout>
</template>
