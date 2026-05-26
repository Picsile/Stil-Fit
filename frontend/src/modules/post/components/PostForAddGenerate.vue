<script setup lang="ts">
import IconAddSimple from '@/components/icons/IconAddSimple.vue'
import IconClose from '@/components/icons/iconClose.vue'
import IconOpen from '@/components/icons/IconOpen.vue'
import { useIsMobile } from '@/composables/useIsMobile'
import type { PostPreviewData } from '@/types/view.types'

interface Props {
  post: PostPreviewData
  isAdded: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'add-item', post: PostPreviewData): void
}>()
</script>

<template>
  <div
    @click.stop="emit('add-item', post)"
    class="group relative block rounded-2xl shadow-(--shadow-base) cursor-pointer"
  >
    <div class="overflow-hidden">
      <img
        :src="post.main_image.path_preview"
        :width="post.main_image.width"
        :height="post.main_image.height"
        :alt="post.title"
        class="select-none rounded-[17px] group-hover:brightness-80 transition-transform"
        :class="{
          'scale-98': isAdded,
        }"
      />
    </div>

    <div
      class="absolute inset-0 w-full h-full border-2 border-(--color-brend-simple) rounded-2xl transition"
      :class="isAdded ? 'opacity-100' : 'opacity-0'"
    >
      <div class="w-full h-full border-3 border-(--color-main-panel) rounded-2xl"></div>
    </div>

    <!-- Open -->
    <div @click.stop="" class="hidden absolute top-2 right-2 group-hover:flex justify-end">
      <router-link
        :to="`/post/${post.id}`"
        class="btn-list flex justify-center! w-auto! aspect-square! bg-(--color-main-panel) p-0!"
      >
        <IconOpen width="22" />
      </router-link>
    </div>

    <!-- Add -->
    <div class="hidden absolute left-0 bottom-0 group-hover:flex w-full p-2">
      <button
        v-if="!isAdded"
        class="btn-accent flex justify-center items-center gap-1 w-full px-3.5"
      >
        <IconAddSimple width="24" />
        <span class="select-none truncate"> В образ </span>
      </button>
      <button v-else class="btn-default gap-1 w-full px-3.5 bg-(--color-main-panel)">
        <IconClose width="20px" />
        <span class="select-none truncate"> Убрать </span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.btn-list,
.btn-accent,
.btn-default {
  height: 38px;
}

.btn-author:hover,
.btn-heart:hover {
  background-color: color-mix(in srgb, var(--color-hover), transparent 60%);
}
</style>
