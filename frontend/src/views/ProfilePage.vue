<script setup lang="ts">
import { useGetProfile } from '@/composables/useGetProfile'
import BoardsList from '@/modules/board/components/BoardsList.vue'
import BoardsListTitle from '@/modules/board/components/BoardsListTitle.vue'
import Header from '@/components/Header.vue'
import IconPhoto from '@/components/icons/IconPhoto.vue'
import IconPlane from '@/components/icons/IconPlane.vue'
import LoaderCat from '@/components/ui/LoaderCat.vue'
import PostsTile from '@/modules/post/components/PostsTile.vue'
import { boardsForProfile } from '@/options/options'
import { useBoardStore } from '@/stores/board.stores'
import { useInfiniteScroll } from '@vueuse/core'
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { countPostsColumns } from '@/utils/countPostsColumns'
import ModalCreateBoard from '@/components/modals/ModalCreateBoard.vue'
import Share from '@/components/ui/Share.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import type { PostPreviewData } from '@/types/view.types'
import { useGetPostsSystemBoard } from '@/composables/useGetPostsSystemBoard'
import type { BoardId } from '@/types/board.types'

const route = useRoute()
const boardStore = useBoardStore()
const userId = ref<number>()

// Состояние
const profile = ref()
const { getProfile } = useGetProfile()
const activeBoardId = ref<string>('my-posts')

// Posts - Копия логики из MyProfilePage
const posts = ref<PostPreviewData[]>([])
const offset = ref<number>(0)
const limit = ref<number>(20)
const countColumns = countPostsColumns(7)
const endPosts = ref(false)

const { isLoadPostsSystemBoard, getPostsSystemBoard } = useGetPostsSystemBoard()
const isLoadPosts = computed(() => isLoadPostsSystemBoard.value)

const getPosts = async (refresh: boolean) => {
  if (refresh) {
    posts.value = []
    offset.value = 0
    endPosts.value = false
  }

  // Вызываем системный метод получения постов
  const newPosts = await getPostsSystemBoard(
    activeBoardId.value,
    offset.value,
    limit.value,
    userId.value,
  )

  if (newPosts.length === 0) endPosts.value = true
  offset.value += limit.value

  posts.value = [...posts.value, ...newPosts]
}

useInfiniteScroll(
  window,
  () => {
    if (!isLoadPosts.value && !endPosts.value) {
      getPosts(false)
    }
  },
  { distance: 600 },
)

// Start
watch(
  () => route.params.userId,
  (newUserId) => {
    if (newUserId) {
      userId.value = Number(newUserId)
      profile.value = getProfile(userId.value)
      boardStore.getBoards(userId.value)
    }
  },
  { immediate: true },
)

watch(
  activeBoardId,
  () => {
    getPosts(true)
  },
  { immediate: true },
)
</script>

<template>
  <MainLayout>
    <Header :is-boards="false" />

    <div class="relative w-full h-40 md:h-60 bg-(--color-input)">
      <img
        v-if="profile?.background_path"
        :src="profile?.background_path"
        alt=""
        class="img-cover"
        fetchpriority="high"
      />
    </div>

    <div class="flex flex-1 w-full bg-(--color-main-panel) px-2 md:px-10">
      <div
        class="flex flex-col gap-10 md:gap-12 items-center w-full -translate-y-16 md:-translate-y-18"
      >
        <div class="relative flex flex-col max-w-200 w-full px-5 md:px-10">
          <div
            class="flex justify-center items-center w-26 md:w-30 aspect-square bg-(--color-input) border-4 border-white rounded-full overflow-hidden"
          >
            <img v-if="profile?.avatar_path" :src="profile?.avatar_path" class="img-cover" />
            <IconPhoto v-else class="stroke-(--color-text-second) w-8 md:w-10" />
          </div>

          <div class="flex flex-col gap-1 md:gap-2 mt-3 md:mt-5">
            <h3 class="text-lg md:text-xl truncate">{{ profile?.login }}</h3>
            <h5 class="text-(--color-text-second) truncate">@{{ profile?.login }}</h5>
          </div>

          <div class="flex gap-5 text-[16px] mt-5 md:mt-7">
            <span class="text-(--color-text-second)">
              <span class="font-bold text-(--color-text) mr-1">{{ boardStore.boards.length }}</span>
              Доски
            </span>
            <span class="text-(--second) text-(--color-text-second)">
              <span class="font-bold text-(--color-text) mr-1">{{ profile?.posts_count }}</span>
              Посты
            </span>
            <span class="text-(--color-text-second)">
              <span class="font-bold text-(--color-text) mr-1">{{ profile?.likes_count }}</span>
              Лайки
            </span>
          </div>

          <Share class="absolute top-18 md:top-24 right-5 md:right-10" />
        </div>

        <div v-if="boardStore.boards.length" class="flex flex-col gap-1 w-full pb-1">
          <span class="flex items-center h-12 font-semibold text-(--color-text-second) text-xl"
            >Доски</span
          >
          <div class="overflow-x-auto relative scrollbar-none flex h-fit">
            <BoardsList :boards="boardStore.boards" />
          </div>
        </div>

        <div class="flex flex-col gap-1 max-w-full w-full h-full pb-6">
          <div class="flex justify-between items-center pb-1">
            <span class="flex items-center h-12 font-semibold text-xl text-(--color-text-second)"
              >Посты</span
            >
          </div>

          <BoardsListTitle
            :boards="[
              ...boardsForProfile,
              ...boardStore.boardTitles.filter((b) => typeof b.id === 'number'),
            ]"
            v-model:active-id="activeBoardId as BoardId"
            class="mx-1 mb-4"
          />

          <div class="relative flex flex-col flex-1 w-full min-h-80 bg-(--color-bg) transition-all">
            <PostsTile :key="activeBoardId" :posts="posts" :countColumns="countColumns" />

            <div v-if="isLoadPosts" class="relative w-full h-40">
              <LoaderCat width="160px" />
            </div>

            <div
              v-if="!isLoadPosts && !posts.length"
              class="absolute top-0 left-0 flex justify-center items-center w-full min-h-full"
            >
              <router-link
                :to="{ name: 'home' }"
                class="flex items-center gap-3 opacity-70 hover:opacity-100 transition"
              >
                <IconPlane width="26" />
                <span class="text-xl">На главную</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
  <ModalCreateBoard />
</template>
