<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useInfiniteScroll, watchDebounced } from '@vueuse/core'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import { useIsMobile } from '@/composables/useIsMobile'
import { countPostsColumns } from '@/utils/countPostsColumns'
import type { BoardId } from '@/types/board.types'
import Header from '@/components/Header.vue'
import UserProfile from '@/components/UserProfile.vue'
import AddButton from '@/components/AddButton.vue'
import LoaderCat from '@/components/ui/LoaderCat.vue'
import InputSearch from '@/components/forms/InputSearch.vue'
import ModalCreateBoard from '@/components/modals/ModalCreateBoard.vue'
import PostsTile from '@/modules/post/components/PostsTile.vue'
import BoardsListTitle from '@/modules/board/components/BoardsListTitle.vue'
import IconUser from '@/components/icons/IconUser.vue'
import type { PostPreviewData } from '@/types/view.types'
import { useGetPostsForBoard } from '@/composables/useGetPostsForBoard'
import { useGetPostsSystemBoard } from '@/composables/useGetPostsSystemBoard'
import { useSearchPosts } from '@/composables/useSearchPosts'

// Состояние
const userStore = useUserStore()
const boardStore = useBoardStore()
const { isMobile } = useIsMobile()

const activeBoardId = ref<BoardId>('top')
const searchQuery = ref<string>('')

// Получение постов
const posts = ref<PostPreviewData[]>([])
const offset = ref<number>(0)
const limit = ref<number>(20)
const countColumns = countPostsColumns(7)
const endPosts = ref(false)

const { isLoadSearchPosts, getSearchPosts } = useSearchPosts()
const { isLoadPostsSystemBoard, getPostsSystemBoard } = useGetPostsSystemBoard()
const { isLoadPostsForBoard, getPostsForBoard } = useGetPostsForBoard()
const isLoadPosts = computed(
  () => isLoadSearchPosts.value || isLoadPostsForBoard.value || isLoadPostsSystemBoard.value,
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
    newPosts = await getPostsForBoard(activeBoardId.value, limit.value)
  }

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

// Отслеживание скролла + анимации
const heroHeight = 400
const scrollY = ref(0)
const boardsHidden = ref(false)
const lastScrollY = ref(0)

const top = computed(() => Math.max(1, 14 - scrollY.value / 15.5 / 1.66))
const searchReached = computed(() => scrollY.value / 16 >= 20)
const heroOpacity = computed(() => {
  const opacity = 1 - scrollY.value / 200
  return Math.max(0, Math.min(1, opacity))
})

const updateScroll = () => {
  scrollY.value = window.scrollY

  if (isMobile.value || scrollY.value <= heroHeight + 10) return

  if (scrollY.value > lastScrollY.value + 20) {
    boardsHidden.value = true
    lastScrollY.value = scrollY.value
  } else if (scrollY.value < lastScrollY.value) {
    boardsHidden.value = false
    lastScrollY.value = scrollY.value
  }
}

// Start
onMounted(() => {
  if (userStore.user?.id) {
    boardStore.getBoards(userStore.user.id)
  }
  window.addEventListener('scroll', updateScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateScroll)
})

// Загрзка новых постов
watch(
  activeBoardId,
  () => {
    window.scrollTo(0, 0)
    searchQuery.value = ''
    getPosts(true)
  },
  { immediate: true },
)

// Поиск
watchDebounced(
  searchQuery,
  () => {
    getPosts(true)
    window.scrollTo(0, 335)
  },
  { debounce: 400 },
)
</script>

<template>
  <div
    class="hidden fixed z-75 top-0 right-0 md:block w-full h-20 will-change-[background-color]"
    :style="{
      backgroundColor: `rgba(255, 255, 255, ${searchReached ? '1' : '0'})`,
    }"
  ></div>

  <!-- Hero -->
  <div
    class="hidden relative overflow-hidden md:flex justify-center transition-all"
    :style="{
      height: `${heroHeight}px`,
      opacity: heroOpacity,
    }"
  >
    <div
      class="hidden absolute md:flex flex-col items-center gap-4"
      :style="{ marginTop: 7 + scrollY / 16 / 1.66 + 'rem' }"
    >
      <h1 class="font-[Caveat] text-5xl">Что ищем сегодня?</h1>
      <span class="font-[ComicRelief] text-[18px] text-(--color-text-second)"
        >Найди свой образ мечты!</span
      >
    </div>

    <img
      src="../../public/site/home_bg.png"
      alt="СтильФит"
      class="absolute -z-1 top-0 w-full object-cover opacity-80"
    />
  </div>

  <!-- Header Two -->
  <div
    class="hidden fixed z-75 md:block transition-[left,right]"
    :class="
      searchReached
        ? userStore.isAccount
          ? 'left-[calc(var(--w-navbar)+1rem)] right-32'
          : 'left-[calc(var(--w-navbar)+1rem)] right-32'
        : 'left-[calc(var(--w-navbar)+20%)] right-[20%]'
    "
    :style="{
      top: top + 'rem',
    }"
  >
    <InputSearch v-model="searchQuery" :show-border="!searchReached" />
  </div>

  <!-- Header There -->
  <div class="hidden fixed z-75 top-0 right-0 md:block p-4 transition">
    <div class="flex items-center gap-2 h-(--h-header)">
      <UserProfile />
      <AddButton />

      <!-- Authorization -->
      <router-link v-if="!userStore.isAccount" :to="{ name: 'login' }" class="btn-accent">
        <IconUser class="shrink-0 mb-0.5" width="20" stroke-width="1.75" />
        <span>Войти</span>
      </router-link>
    </div>
  </div>

  <Header
    class="md:hidden"
    :boards="boardStore.boardTitles"
    v-model:active-board-id="activeBoardId"
  />

  <!-- Content -->
  <div class="min-h-screen" :class="!userStore.isAccount && 'mt-8.25! md:mt-0!'">
    <div
      v-if="userStore.isAccount && !isMobile"
      class="sticky z-50 top-16 bg-(--color-main-panel) px-6 py-4 translate-y-0 transition-all duration-300"
      :class="boardsHidden && 'top-5!'"
    >
      <BoardsListTitle :boards="boardStore.boardTitles" v-model:active-id="activeBoardId" />
    </div>

    <PostsTile
      :posts="posts"
      :countColumns="countColumns"
      :offset="true"
      class="px-1 md:px-4"
      :class="!userStore.isAccount ? 'pt-8' : ''"
    />
    <div class="relative w-full h-40">
      <LoaderCat v-if="isLoadPostsForBoard || isLoadPostsSystemBoard" width="150rem" />
    </div>
  </div>

  <ModalCreateBoard />
</template>
