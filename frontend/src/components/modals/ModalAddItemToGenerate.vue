<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import BoardListTitle from '../../modules/board/components/BoardsListTitle.vue'
import Close from '../ui/Close.vue'
import InputSearch from '../forms/InputSearch.vue'
import IconShirt from '@/components/icons/IconShirt.vue'
import type { PostPreviewData } from '@/types/view.types'
import { viewApi } from '@/api/api'
import LoaderCat from '../ui/LoaderCat.vue'
import PostsTileCreate from '@/modules/post/components/PostsTileAddItem.vue'
import type { BoardId, BoardTitleData } from '@/types/board.types'
import { useInfiniteScroll, watchDebounced } from '@vueuse/core'
import { countPostsColumns } from '@/utils/countPostsColumns'
import { useGetPostsInBoard } from '@/composables/useGetPostsInBoard'
import { useSearchPosts } from '@/composables/useSearchPosts'
import { useGetPostsSystemBoard } from '@/composables/useGetPostsSystemBoard'

interface Props {
  addedItemIds: number[]
  boardTitles: BoardTitleData[]
}

const props = defineProps<Props>()

const isAddItems = defineModel<boolean>('isAddItems')

const activeBoardId = defineModel<BoardId>('activeBoardId', { default: 'top' })
const searchQuery = ref<string>('')

const emit = defineEmits<{
  (e: 'add-item', post: PostPreviewData): void
}>()

// Получение постов
const posts = ref<PostPreviewData[]>([])
const offset = ref<number>(0)
const limit = ref<number>(20)
const countColumns = countPostsColumns(7)
const scrollContainer = ref<HTMLElement | null>(null)
const endPosts = ref(false)

const { isLoadSearchPosts, getSearchPosts } = useSearchPosts()
const { isLoadPostsSystemBoard, getPostsSystemBoard } = useGetPostsSystemBoard()
const { isLoadPostsInBoard, getPostsInBoard } = useGetPostsInBoard()
const isLoadPosts = computed(
  () => isLoadSearchPosts.value || isLoadPostsInBoard.value || isLoadPostsSystemBoard.value,
)

const getPosts = async (refresh: boolean) => {
  if (refresh) {
    posts.value = []
    offset.value = 0
    endPosts.value = false
  }

  let newPosts: PostPreviewData[] = []

  if (searchQuery.value) {
    newPosts = await getSearchPosts(searchQuery.value, offset.value, limit.value)
  } else if (typeof activeBoardId.value === 'string') {
    newPosts = await getPostsSystemBoard(activeBoardId.value, offset.value, limit.value)
  } else {
    newPosts = await getPostsInBoard(activeBoardId.value, offset.value, limit.value)
  }

  if (newPosts.length === 0) endPosts.value = true
  offset.value += limit.value

  posts.value = [...posts.value, ...newPosts]
}

useInfiniteScroll(
  scrollContainer,
  () => {
    if (!isLoadPosts.value && !endPosts.value) {
      getPosts(false)
    }
  },
  { distance: 400 },
)

// Загрзка новых постов
watch(
  activeBoardId,
  () => {
    getPosts(true)
  },
  { immediate: true },
)

// Поиск
watchDebounced(
  searchQuery,
  () => {
    getPosts(true)
    console.log(1)
  },
  { debounce: 400 },
)
</script>

<template>
  <Transition name="modal">
    <div
      @click="isAddItems = false"
      class="fixed z-200 left-0 top-0 flex justify-center items-center w-screen h-screen bg-slate-900/50 p-1 md:p-4"
    >
      <div
        @click.stop
        class="overflow-hidden flex flex-col gap-4 w-full max-w-[80%] h-[90%] bg-(--color-main-panel) p-4 md:p-8 rounded-3xl"
      >
        <!-- Header -->
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <IconShirt />
            <h3 class="text-lg">
              <span class="text-(--color-blue)">Добавьте вещь</span> в генератор
            </h3>
          </div>
          <Close @click="isAddItems = false" class="w-7" />
        </div>
        <InputSearch v-model="searchQuery" class="shrink-0" />

        <div class="pb-0.5">
          <BoardListTitle :boards="boardTitles" v-model:active-id="activeBoardId" class="mx-1" />
        </div>

        <div
          ref="scrollContainer"
          class="overflow-y-scroll relative scrollbar-none w-full flex-1 min-h-0"
        >
          <PostsTileCreate
            v-if="posts"
            :posts="posts"
            :count-columns="countColumns"
            @add-item="emit('add-item', $event)"
            :added-item-ids="addedItemIds"
          />

          <div class="relative w-full h-40">
            <LoaderCat v-if="isLoadPosts" width="140px" />
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>
