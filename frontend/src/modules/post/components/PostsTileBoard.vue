<script setup lang="ts">
import PostsColumnBoard from './PostsColumnBoard.vue'
import type { PostPreviewData } from '@/types/view.types'
import { ref, watch } from 'vue'

interface Props {
  posts: PostPreviewData[]
  countColumns: number
  offset?: boolean
}

const props = defineProps<Props>()

const columns = ref<PostPreviewData[][]>(Array.from({ length: props.countColumns }, () => []))

const columnHeights = ref<number[]>(Array.from({ length: props.countColumns }, () => 0))

let lastPostIdx = ref<number>(0)

const distributePosts = (posts: PostPreviewData[]) => {
  const newPosts = posts.slice(lastPostIdx.value)

  newPosts.forEach((post) => {
    const shortestColumnIndex = columnHeights.value.indexOf(Math.min(...columnHeights.value))

    if (columns.value[shortestColumnIndex] && columnHeights.value[shortestColumnIndex]) {
      columns.value[shortestColumnIndex].push(post)
      columnHeights.value[shortestColumnIndex] += post.main_image.height
    }
  })

  lastPostIdx.value = posts.length
}

watch(
  () => props.posts,
  (newPosts, oldPosts) => {
    if (newPosts !== oldPosts) {
      lastPostIdx.value = 0
      columns.value = Array.from({ length: props.countColumns }, () => [])
      columnHeights.value = Array.from({ length: props.countColumns }, () => 0)
    }
    distributePosts(newPosts)
  },
  { immediate: true, deep: true },
)

watch(
  () => props.countColumns,
  () => {
    lastPostIdx.value = 0
    columns.value = Array.from({ length: props.countColumns }, () => [])
    columnHeights.value = Array.from({ length: props.countColumns }, () => 0)
    distributePosts(props.posts)
  },
)
</script>

<template>
  <div class="flex gap-4 p-4 w-full">
    <PostsColumnBoard
      v-for="(column, index) in columns"
      :key="index"
      :posts="column"
      :offset="offset && index % 2 === 1"
    />
  </div>
</template>
