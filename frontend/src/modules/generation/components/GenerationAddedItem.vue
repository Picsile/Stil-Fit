<script setup lang="ts">
import type { PostPreviewData } from '@/types/view.types'
import RemoveItem from '@/components/ui/RemoveElem.vue'
import Close from '@/components/ui/Close.vue'
import IconClose from '@/components/icons/iconClose.vue'

interface Props {
  post: PostPreviewData
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'add-item', post: PostPreviewData): void
}>()
</script>

<template>
  <div
    class="group relative overflow-hidden aspect-square w-28 rounded-xl cursor-pointer"
  >
    <router-link :to="`/post/${post.id}`">
      <img
        :src="post.main_image.path_preview"
        :alt="post.title"
        class="w-full h-full object-cover hover:brightness-75 transition"
      />
    </router-link>

    <div
      @click="emit('add-item', post)"
      class="btn-opacity-dark absolute top-1.5 right-1.5 h-auto! aspect-square bg-transparent! hover:bg-(--color-dark)/50! p-1 rounded-lg! md:opacity-0 group-hover:opacity-100"
    >
      <IconClose class="w-5" stroke-width="1.8" />
    </div>
  </div>
</template>
