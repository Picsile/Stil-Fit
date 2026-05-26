<script setup lang="ts">
import type { PostPreviewData } from '@/types/view.types'
import PostForCreate from './PostForAddGenerate.vue'
import { ref, watch } from 'vue'
import { useIsMobile } from '@/composables/useIsMobile'
import { countPostsColumns } from '@/utils/countPostsColumns'

interface Props {
  posts: PostPreviewData[]
  countColumns: number
  addedItemIds: number[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'add-item', post: PostPreviewData): void
}>()

const columns = ref<PostPreviewData[][]>(Array.from({ length: props.countColumns }, () => []))

let lastPostIdx = ref<number>(0)

const addNewPosts = (posts: PostPreviewData[]) => {
  const newPosts = posts.slice(lastPostIdx.value)

  newPosts.forEach((post) => {
    const shortColumn = columns.value.reduce((minColumn, currentColumn) => {
      return currentColumn.length < minColumn.length ? currentColumn : minColumn
    })
    shortColumn.push(post)
  })

  lastPostIdx.value = posts.length
}

watch(
  () => props.posts,
  (newPosts, oldPosts) => {
    if (newPosts !== oldPosts) {
      lastPostIdx.value = 0
      columns.value = Array.from({ length: props.countColumns }, () => [])
    }
    addNewPosts(newPosts)
  },
  { immediate: true, deep: true },
)

watch(
  () => props.countColumns,
  () => {
    lastPostIdx.value = 0
    columns.value = Array.from({ length: props.countColumns }, () => [])
    addNewPosts(props.posts)
  },
)
</script>

<template>
  <div class="flex gap-1 md:gap-4 w-full">
    <div
      v-for="(column, index) in columns"
      :key="index"
      v-auto-animate
      class="flex flex-col gap-1 md:gap-4 flex-1"
    >
      <PostForCreate
        v-for="post in column"
        :key="post.id"
        :post="post"
        :is-added="addedItemIds.includes(post.id)"
        @add-item="emit('add-item', post)"
      />
    </div>
  </div>
</template>
