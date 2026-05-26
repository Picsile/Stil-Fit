<script setup lang="ts">
import { viewApi } from '@/api/api'
import { useGetBoard } from '@/composables/useGetBoard'
import Header from '@/components/Header.vue'
import IconAvatar from '@/components/icons/IconAvatar.vue'
import Navbar from '@/components/Navbar.vue'
import LoaderCat from '@/components/ui/LoaderCat.vue'
import type { BoardData, BoardId } from '@/types/board.types'
import { countPostsColumns } from '@/utils/countPostsColumns'
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import { useInfiniteScroll } from '@vueuse/core'
import AppDesktopLayout from '@/layouts/AppDesktopLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import ModalCreateBoard from '@/components/modals/ModalCreateBoard.vue'
import ModalDeleteBoard from '@/components/modals/ModalDeleteBoard.vue'
import PostsTile from '@/modules/post/components/PostsTile.vue'
import Share from '@/components/ui/Share.vue'
import IconTrash from '@/components/icons/IconTrash.vue'
import { useRouter } from 'vue-router'
import { useGetPostsInBoard } from '@/composables/useGetPostsInBoard'
import type { PostPreviewData } from '@/types/view.types'

// Состояние
const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const boardStore = useBoardStore()

const { isLoadBoard, getBoard } = useGetBoard()

// Board
const boardId = ref<number>()
const board = ref<BoardData>()

// Получение постов
const posts = ref<PostPreviewData[]>([])
const offset = ref<number>(0)
const limit = ref<number>(20)
const countColumns = countPostsColumns(7)
const endPosts = ref(false)

const { isLoadPostsInBoard, getPostsInBoard } = useGetPostsInBoard()
const isLoadPosts = computed(() => isLoadPostsInBoard.value)

const getPosts = async (refresh: boolean) => {
  if (refresh) {
    posts.value = []
    offset.value = 0
    endPosts.value = false
    console.log('Обнуление')
  }

  let newPosts: PostPreviewData[] = []
  newPosts = await getPostsInBoard(boardId.value!, offset.value, limit.value)

  if (newPosts.length < limit.value) endPosts.value = true
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

// Delete board
const isOwner = computed(() => {
  return userStore.user?.id === board.value?.author.id
})

const handleDeleteBoard = () => {
  if (!isOwner.value || !boardId.value) return
  boardStore.openDeleteModal(Number(boardId.value))
}

const confirmDelete = async () => {
  if (!boardStore.deleteBoardId) return

  const result = await boardStore.deleteBoard(boardStore.deleteBoardId)

  if (result.status === 'success') {
    await router.push({ name: 'home' })
  }
}

// Start
watch(
  () => route.params.boardId,
  async (newBoardId) => {
    boardId.value = Number(newBoardId)

    board.value = await getBoard(boardId.value)

    if (userStore.user?.id) {
      boardStore.getBoards(userStore.user.id)
    }
  },
  { immediate: true },
)
</script>

<template>
  <MainLayout content-padding>
    <Header :is-boards="false" />

    <!-- Шапка доски -->
    <div class="flex flex-wrap justify-between gap-2.5 px-4 md:px-6 pt-6 pb-4 md:pb-6">
      <div class="flex flex-col">
        <h3 class="font-[ComicRelief] text-2xl tracking-wider truncate">
          {{ board?.title }}aaaaaaaaaaaaaaaaaaaaaaaa
        </h3>

        <span class="text-lg text-(--color-text-second) mt-2"
          ><span class="font-bold text-(--color-text) mr-1">{{ posts?.length }}</span> посты</span
        >

        <router-link
          v-if="board?.author"
          :to="{ name: 'profile', params: { userId: board?.author.id } }"
          class="btn-fit gap-1.5 w-fit p-2 pr-3 -ml-2 mt-1"
        >
          <div>
            <img
              v-if="board?.author.avatar_path"
              :src="board?.author.avatar_path"
              :alt="board?.author.login"
              class="img-cover w-8! h-8! rounded-full"
            />

            <div
              v-else
              class="flex justify-center items-center w-8 bg-(--color-input) p-2 rounded-full"
            >
              <IconAvatar width="24" class="stroke-(--color-hover-dark)" />
            </div>
          </div>
          <span>{{ board?.author.login }}</span>
        </router-link>
      </div>

      <!-- Кнопки -->
      <div class="flex gap-2">
        <Share />
        <button
          v-if="isOwner"
          @click="handleDeleteBoard()"
          class="btn-default h-10! border border-(--color-border)"
        >
          <IconTrash width="21" />
        </button>
      </div>
    </div>

    <div class="relative flex flex-1 w-full min-h-70 px-4 transition-all">
      <PostsTile v-if="posts.length" :key="boardId" :posts="posts" :countColumns="countColumns" />

      <LoaderCat v-if="isLoadPosts" width="160px" />

      <div
        v-if="!isLoadPosts && !posts.length"
        class="flex justify-center items-center w-full min-h-full text-xl opacity-70"
      >
        <span>В этой доске пока нет пинов</span>
      </div>
    </div>
  </MainLayout>

  <ModalCreateBoard />
  <ModalDeleteBoard @confirm="confirmDelete()" />
</template>
