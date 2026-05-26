<script setup lang="ts">
import type { ImageData, PostPreviewData } from '@/types/view.types'
import { useImageStore } from '@/stores/image.stores'
import { useGenerationDataStore } from '@/stores/generationData.stores'
import type { GeneratedImageData } from '@/types/generation.types'

interface Props {
  imageData: ImageData
  allImages?: ImageData[]
  generationItems?: PostPreviewData[]
  generationPrompt?: string
}

const props = defineProps<Props>()
const imageStore = useImageStore()
const generationDataStore = useGenerationDataStore()

const openImage = () => {
  const allImageUrls =
    props.allImages?.map((img) => img.path).filter((path): path is string => !!path) || []

  imageStore.openImage(props.imageData.path!, allImageUrls)
}

const handleToggle = () => {
  const imageWithItems: GeneratedImageData = {
    ...(props.imageData as GeneratedImageData),
    generationItems: props.generationItems || [],
  }
  generationDataStore.toggleImage(imageWithItems, props.generationPrompt || '')
}
</script>

<template>
  <div class="overflow-hidden relative rounded-xl cursor-pointer shadow-(--shadow-light)">
    <img
      @click="openImage"
      :src="imageData.path"
      :alt="String(imageData.id)"
      class="hover:brightness-75 transition duration-400"
    />

    <input
      type="checkbox"
      :checked="generationDataStore.isImageSelected(imageData.id)"
      @change="handleToggle"
      @click.stop=""
      class="absolute top-2 right-2 appearance-none w-4.5 aspect-square bg-(--color-dark)! border border-(--color-input) rounded checked:bg-blue-500! opacity-30 checked:opacity-100! after:content-['\2714'] after:flex after:items-center after:justify-center after:text-white after:text-[10px] after:opacity-0 checked:after:opacity-100"
    />
  </div>
</template>
