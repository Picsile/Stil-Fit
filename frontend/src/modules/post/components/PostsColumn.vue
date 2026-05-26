<script setup lang="ts">
import Post from './Post.vue'
import type { PostPreviewData } from '@/types/view.types'

interface Props {
  posts: PostPreviewData[]
  offset?: boolean
}

defineProps<Props>()
</script>

<template>
  <div
    v-auto-animate
    class="flex flex-col gap-1 md:gap-4 flex-1"
    :class="offset ? 'mt-18' : ''"
  >
    <Post
      v-for="post in posts"
      :key="post.id"
      :post="post"
    />
  </div>
</template>
