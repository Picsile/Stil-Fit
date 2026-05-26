<script setup lang="ts">
import { viewApi, accountApi } from '@/api/api'
import { useGetProfile } from '@/composables/useGetProfile'
import BoardsList from '@/modules/board/components/BoardsList.vue'
import BoardsListTitle from '@/modules/board/components/BoardsListTitle.vue'
import Header from '@/components/Header.vue'
import IconAddImage from '@/components/icons/IconAddImage.vue'
import IconAddSimple from '@/components/icons/IconAddSimple.vue'
import IconOpen from '@/components/icons/IconOpen.vue'
import IconPhoto from '@/components/icons/IconPhoto.vue'
import IconPlane from '@/components/icons/IconPlane.vue'
import LoaderCat from '@/components/ui/LoaderCat.vue'
import Navbar from '@/components/Navbar.vue'
import PostsTile from '@/modules/post/components/PostsTile.vue'
import { boardsForMyProfile } from '@/options/options'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import type { BoardId } from '@/types/board.types'
import { useInfiniteScroll } from '@vueuse/core'
import { computed, ref, useTemplateRef, watch } from 'vue'
import AppDesktopLayout from '@/layouts/AppDesktopLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import LoaderCircle from '@/components/ui/LoaderCircle.vue'
import { countPostsColumns } from '@/utils/countPostsColumns'
import ModalCreateBoard from '@/components/modals/ModalCreateBoard.vue'
import Share from '@/components/ui/Share.vue'
import type { PostPreviewData } from '@/types/view.types'
import { useGetPostsSystemBoard } from '@/composables/useGetPostsSystemBoard'
import type { UserData } from '@/types/auth.types'

// Состояние
const userStore = useUserStore()
const boardStore = useBoardStore()
const profile = ref()

const { isLoadProfile, getProfile } = useGetProfile()
const activeBoardId = ref('my-posts')

// Posts
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

  const newPosts = await getPostsSystemBoard(activeBoardId.value, offset.value, limit.value)

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

// Avatar
const avatarRef = useTemplateRef<HTMLInputElement>('avatar')

const loadAvatar = () => {
  avatarRef.value?.click()
}

const isLoadAvatar = ref<boolean>(false)

const saveAvatar = async (event: Event) => {
  const target = event.target as HTMLInputElement

  const avatarFile = target.files?.[0]

  if (!avatarFile) return

  try {
    isLoadAvatar.value = true

    const formData = new FormData()
    formData.append('avatarFile', avatarFile)

    const data = await accountApi.uploadAvatar(formData)

    if (data.status === 'success') {
      if (userStore.user) {
        userStore.user.avatar_path = data.avatar_path
      }
    } else {
      throw new Error(data.message)
    }
  } catch (error) {
    console.error('Ошибка при загрузке аватарки: ', error)
  } finally {
    isLoadAvatar.value = false
    target.value = ''
  }
}

// BG image
const bgImageRef = useTemplateRef<HTMLInputElement>('bgImage')

const loadBgImage = () => {
  bgImageRef.value?.click()
}

const isLoadBgImage = ref<boolean>(false)

const saveBgImage = async (event: Event) => {
  const target = event.target as HTMLInputElement

  const bgImageFile = target.files?.[0]

  if (!bgImageFile) return

  try {
    isLoadBgImage.value = true

    const formData = new FormData()
    formData.append('bgImageFile', bgImageFile)

    const data = await accountApi.uploadBgImage(formData)

    if (data.status === 'success') {
      if (userStore.user) {
        userStore.user.background_path = data.background_path
      }
    } else {
      throw new Error(data.message)
    }
  } catch (error) {
    console.error('Ошибка при загрузке фонвой картинки: ', error)
  } finally {
    isLoadBgImage.value = false
    target.value = ''
  }
}

// Start
watch(
  () => userStore.user?.id,
  (newUserId) => {
    if (newUserId) {
      profile.value = getProfile(newUserId)
      boardStore.getBoards(newUserId)
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
  <MainLayout class="border-5">
    <Header :is-boards="false" />

    <!-- Header image -->
    <div class="relative w-full h-40 md:h-60 bg-(--color-input)">
      <img
        v-if="userStore.user?.background_path"
        :src="userStore.user?.background_path"
        :alt="userStore.user?.login"
        class="img-cover"
        fetchpriority="high"
      />

      <button
        type="button"
        @click="loadBgImage"
        class="btn-default absolute top-2 md:top-4 right-2 md:right-10 flex items-center gap-3 h-11! bg-(--color-main-panel)/40 hover:bg-(--color-main-panel)! px-4"
      >
        <IconAddImage width="21" />
      </button>

      <input
        ref="bgImage"
        type="file"
        accept=".jpg, .jpeg, .png, .webp"
        @change="saveBgImage"
        class="hidden"
      />

      <LoaderCircle v-if="isLoadBgImage" />
    </div>

    <!-- Bg white -->
    <div class="flex flex-1 w-full bg-(--color-main-panel) px-2 md:px-10">
      <!-- Up -->
      <div
        class="flex flex-col gap-10 md:gap-12 items-center w-full -translate-y-16 md:-translate-y-18"
      >
        <!-- User -->
        <div class="relative flex flex-col max-w-200 w-full px-5 md:px-10">
          <!-- Avatar -->
          <button
            type="button"
            class="btn-fit relative overflow-hidden justify-center! items-center w-26 md:w-30 aspect-square bg-(--color-input) p-0! border-4 border-white rounded-full!"
            @click="loadAvatar"
          >
            <img
              v-if="userStore.user?.avatar_path"
              :src="userStore.user?.avatar_path"
              :alt="userStore.user?.login"
              class="img-cover hover:brightness-90 rounded-full"
            />

            <IconPhoto v-else class="stroke-(--color-text-second) w-8 md:w-10" />

            <input
              ref="avatar"
              type="file"
              accept=".jpg, .jpeg, .png, .webp"
              @change="saveAvatar"
              class="hidden"
            />

            <LoaderCircle v-if="isLoadAvatar" width="40px" />
          </button>

          <div class="flex flex-col gap-1 md:gap-2 mt-3 md:mt-5">
            <h3 class="text-lg truncate">{{ userStore.user?.login }}</h3>
            <h5 class="text-(--color-text-second) truncate">@{{ userStore.user?.login }}</h5>
          </div>

          <div class="flex gap-5 text-[16px] mt-5 md:mt-7">
            <span class="text-(--color-text-second)"
              ><span class="font-bold text-(--color-text) mr-1">{{
                boardStore.boards.length
              }}</span>
              Доски</span
            >
            <span class="text-(--color-text-second)"
              ><span class="font-bold text-(--color-text) mr-1">{{ profile?.posts_count }}</span>
              Посты</span
            >
            <span class="text-(--color-text-second)"
              ><span class="font-bold text-(--color-text) mr-1">{{ profile?.likes_count }}</span>
              Лайки</span
            >
          </div>

          <Share class="absolute top-18 md:top-24 right-5 md:right-10" />
        </div>

        <!-- Boards -->
        <div v-if="boardStore.boards.length" class="flex flex-col gap-1 w-full pb-1">
          <span class="flex items-center h-12 font-semibold text-(--color-text-second) text-xl"
            >Доски</span
          >

          <div class="overflow-x-auto relative scrollbar-none flex h-fit">
            <BoardsList :boards="boardStore.boards" />
          </div>
        </div>

        <!-- Posts -->
        <div class="flex flex-col gap-1 max-w-full w-full h-full pb-6">
          <!-- Header -->
          <div class="flex justify-between items-center pb-1">
            <span class="flex items-center h-12 font-semibold text-xl text-(--color-text-second)"
              >Посты</span
            >
            <router-link
              :to="{ name: 'public' }"
              class="btn-default btn-accent flex gap-2.5 h-10! px-4"
            >
              Создать пост
            </router-link>
          </div>

          <!-- Boards titles  -->
          <BoardsListTitle
            :boards="[...boardsForMyProfile]"
            v-model:active-id="activeBoardId as BoardId"
            class="mx-1 mb-4"
          />

          <!-- Posts tile -->
          <div class="relative flex flex-col flex-1 w-full min-h-80 bg-(--color-bg) transition-all">
            <PostsTile :key="activeBoardId" :posts="posts" :countColumns="countColumns" />
            <div v-if="isLoadPosts" class="relative w-full h-40">
              <LoaderCat width="160px" />
            </div>

            <!-- Undefind -->
            <div
              v-if="!isLoadPosts && !posts.length"
              class="absolute top-0 left-0 flex justify-center items-center w-full min-h-full"
            >
              <router-link
                :to="{ name: 'home' }"
                class="flex items-center gap-3 opacity-70 hover:opacity-100 transition cursor-pointer"
              >
                <IconPlane width="26" stroke-width="1.8" />
                <span class="text-xl"
                  >Навстречу
                  <span class="text-(--color-blue)">впечатлениям</span>
                  !</span
                >
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>

  <ModalCreateBoard />
</template>
