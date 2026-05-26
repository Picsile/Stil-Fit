<script setup lang="ts">
import { useIsMobile } from '@/composables/useIsMobile'
import PostsColumn from './PostsColumn.vue'
import type { PostPreviewData } from '@/types/view.types'
import { ref, watch } from 'vue'

const { isMobile } = useIsMobile()

interface Props {
  posts: PostPreviewData[]
  countColumns: number
  offset?: boolean
}

const props = defineProps<Props>()

const columns = ref<PostPreviewData[][]>(Array.from({ length: props.countColumns }, () => []))

const columnHeights = ref<number[]>(Array.from({ length: props.countColumns }, () => 0))

let lastPostIdx = ref<number>(0)

const getPostHeightWeight = (post: PostPreviewData): number => {
  const width = post.main_image.width || 1
  const height = post.main_image.height || 1

  return height / width
}

const distributePosts = (posts: PostPreviewData[]) => {
  const newPosts = posts.slice(lastPostIdx.value)

  newPosts.forEach((post) => {
    const shortestColumnIndex = columnHeights.value.indexOf(Math.min(...columnHeights.value))
    if (shortestColumnIndex < 0) return

    const targetColumn = columns.value[shortestColumnIndex]
    if (!targetColumn) return

    targetColumn.push(post)
    columnHeights.value[shortestColumnIndex] =
      (columnHeights.value[shortestColumnIndex] ?? 0) + getPostHeightWeight(post)
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
  <div class="flex gap-1 md:gap-4 w-full">
    <PostsColumn
      v-for="(column, index) in columns"
      :key="index"
      :posts="column"
      :offset="!isMobile && offset && index % 2 === 1"
    />
  </div>
</template>
