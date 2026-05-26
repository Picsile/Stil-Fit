<script setup lang="ts">
import { accountApi } from '@/api/api'
import { useGetOutfitsWithItem } from '@/composables/useGetOutfitsWithItem'
import { useGetPost } from '@/composables/useGetPost'
import { useGetSimilarPosts } from '@/composables/useGetSimilarPosts'
import { usePostLike } from '@/composables/usePostLike'
import { useSavePostToBoard } from '@/composables/useSavePostToBoard'
import Header from '@/components/Header.vue'
import IconAdd from '@/components/icons/IconAdd.vue'
import IconAddSimple from '@/components/icons/IconAddSimple.vue'
import IconAvatar from '@/components/icons/IconAvatar.vue'
import IconChevronLeft from '@/components/icons/IconChevronLeft.vue'
import IconChevronRight from '@/components/icons/IconChevronRight.vue'
import IconFavorite from '@/components/icons/IconFavorite.vue'
import IconHeart from '@/components/icons/IconHeart.vue'
import IconLink from '@/components/icons/IconLink.vue'
import IconSuccess from '@/components/icons/IconSuccess.vue'
import AppDesktopLayout from '@/layouts/AppDesktopLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import Navbar from '@/components/Navbar.vue'
import ArrowBack from '@/components/ui/ArrowBack.vue'
import LoaderCircle from '@/components/ui/LoaderCircle.vue'
import PostsTile from '@/modules/post/components/PostsTile.vue'
import ModalSavePost from '@/components/modals/ModalSavePost.vue'
import ModalCreateBoard from '@/components/modals/ModalCreateBoard.vue'
import ModalDeletePost from '@/components/modals/ModalDeletePost.vue'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import { useImageStore } from '@/stores/image.stores'
import { useGenerationItems } from '@/composables/useGenerationItems'
import { useDeletePost } from '@/composables/useDeletePost'
import { useAuthRedirect } from '@/composables/useAuthRedirect'
import { useSwiper } from '@/composables/useSwiper'
import type { PostData, PostPreviewData } from '@/types/view.types'
import { countPostsColumns } from '@/utils/countPostsColumns'
import { onClickOutside, useElementSize, useInfiniteScroll, useWindowSize } from '@vueuse/core'
import { computed, onMounted, ref, useTemplateRef, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, EffectCoverflow } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/effect-coverflow'
import IconTrash from '@/components/icons/IconTrash.vue'
import IconOpen from '@/components/icons/IconOpen.vue'
import { useIsMobile } from '@/composables/useIsMobile'
import IconBrush from '@/components/icons/IconBrush.vue'
import Share from '@/components/ui/Share.vue'
import IconArrowBack from '@/components/icons/IconArrowBack.vue'

// Состояния
const { isMobile } = useIsMobile()
const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const boardStore = useBoardStore()
const imageStore = useImageStore()
const { requireAuth } = useAuthRedirect()

// Пост
const postId = ref<number>()
const post = ref<PostData>()
const { isLoadPost, getPost } = useGetPost()

// Удаление поста
const isDeletePostModal = ref(false)
const { isLoadDeletePost, deletePost } = useDeletePost()

const confirmDeletePost = async () => {
  if (postId.value && (await deletePost(postId.value))) {
    router.push('/')
  }
}

// Вещи в посте
function formatLink(url: string): string {
  const newUrl = new URL(url)
  const cleanUrl = newUrl.hostname.replace('www.', '')

  return cleanUrl.replace(/\/$/, '')
}

const { like } = usePostLike(post)

// Сохранить в доску
const isLastColumn = ref<boolean>(false)
const isSavePost = ref<boolean>(false)
const saveModalRef = useTemplateRef<HTMLElement>('saveModal')
const saveRef = useTemplateRef<HTMLElement>('save')
const { saveBoardIds, savePost } = useSavePostToBoard()

const toggleSaveModal = () => {
  if (!requireAuth()) return

  isSavePost.value = !isSavePost.value

  if (isSavePost.value && saveRef.value) {
    isLastColumn.value = saveRef.value.getBoundingClientRect().right > 1700
  }
}

onClickOutside(
  saveModalRef,
  () => {
    if (boardStore.isCreateModalOpen) return
    isSavePost.value = false
  },
  {
    ignore: [saveRef],
  },
)

const { isItemAdded, toggleItem } = useGenerationItems()

// Images
const imagePreviewIdx = ref<number>(0)
const { onSwiper: onThumbnailSwiper } = useSwiper()
const { swiperInstance: mainSwiper, onSwiper: onMainSwiper, slideTo: mainSlideTo } = useSwiper()

const selectImage = (index: number) => {
  imagePreviewIdx.value = index
  mainSlideTo(index)
}

const onMainSlideChange = (swiper: any) => {
  imagePreviewIdx.value = swiper.activeIndex
}

const openImage = (index: number) => {
  if (!post.value?.images) return
  const allImageUrls = post.value.images
    .map((img) => img.path)
    .filter((path): path is string => !!path)
  const currentPath = post.value.images[index]?.path
  if (currentPath) {
    imageStore.openImage(currentPath, allImageUrls)
  }
}

// Образы с вещью
const { onSwiper: onOutfitSwiper } = useSwiper()
const swiperModules = [Navigation, EffectCoverflow]
const { isLoadOutfits, getOutfitsWithItem } = useGetOutfitsWithItem()

const outfits = ref<PostPreviewData[]>([])
const outfitsOffset = ref(0)
const outfitsLimit = ref(20)

// Похожие посты
const { isLoadPosts, getSimilarPosts } = useGetSimilarPosts()

const posts = ref<PostPreviewData[]>([])
const postsOffset = ref(0)
const postsLimit = ref(30)
const endPosts = ref(false)

// Расчёт показа правой колонки
const { width: windowWidth } = useWindowSize()
const baseWindowWidth = 1920
const rightColumnVisibleRatio = 0.8
const isRightColumn = computed(() => windowWidth.value >= baseWindowWidth * rightColumnVisibleRatio)

// Расчёт ширины правой колонки
const countPostsInRowLeft = countPostsColumns(6)
const countPostsInRowRigth = countPostsColumns(1)

const postsLayoutRef = useTemplateRef<HTMLElement>('postsLayout')
const { width: postsLayoutWidth } = useElementSize(postsLayoutRef)

const rightPostsTileWidth = computed(() => {
  const innerGapPx = 16

  const cardWidth =
    (postsLayoutWidth.value -
      (countPostsInRowLeft.value + countPostsInRowRigth.value - 1) * innerGapPx) /
    (countPostsInRowLeft.value + countPostsInRowRigth.value)

  return cardWidth * countPostsInRowRigth.value + (countPostsInRowRigth.value - 1) * innerGapPx
})

// Получение постов
const getPosts = async () => {
  if (!postId.value) return

  let newPosts: PostPreviewData[] = []

  newPosts = await getSimilarPosts(postId.value, postsOffset.value, postsLimit.value)

  if (newPosts.length === 0) endPosts.value = true
  postsOffset.value += postsLimit.value

  posts.value = [...posts.value, ...newPosts]
}

useInfiniteScroll(
  window,
  () => {
    if (!isLoadPosts.value && !endPosts.value) {
      getPosts()
    }
  },
  { distance: 600 },
)

// Разбивка
const postsLeft = ref<PostPreviewData[]>([])
const postsRight = ref<PostPreviewData[]>([])
const distributedPostsCount = ref(0)

const resetSimilarPostsDistribution = () => {
  distributedPostsCount.value = 0
  postsLeft.value = []
  postsRight.value = []
}

const appendSimilarPosts = (newPosts: PostPreviewData[]) => {
  if (!newPosts.length) return

  if (!isRightColumn.value) {
    postsRight.value.push(...newPosts)
    distributedPostsCount.value += newPosts.length
    return
  }

  const rightColumnPriorityCount = countPostsInRowRigth.value * 3
  const columnsCount = countPostsInRowLeft.value + countPostsInRowRigth.value

  newPosts.forEach((similarPost) => {
    const index = distributedPostsCount.value

    if (index < rightColumnPriorityCount) {
      postsRight.value.push(similarPost)
    } else {
      const cycleIndex = (index - rightColumnPriorityCount) % columnsCount

      if (cycleIndex < countPostsInRowRigth.value) {
        postsRight.value.push(similarPost)
      } else {
        postsLeft.value.push(similarPost)
      }
    }

    distributedPostsCount.value += 1
  })
}

const redistributeSimilarPosts = () => {
  resetSimilarPostsDistribution()
  appendSimilarPosts(posts.value)
}

watch(posts, (newPosts, oldPosts) => {
  const isRefresh =
    !oldPosts ||
    newPosts.length < oldPosts.length ||
    (oldPosts.length > 0 && newPosts[0]?.id !== oldPosts[0]?.id)

  if (isRefresh) {
    redistributeSimilarPosts()
    return
  }

  appendSimilarPosts(newPosts.slice(oldPosts.length))
})

watch([isRightColumn, countPostsInRowLeft, countPostsInRowRigth], () => {
  redistributeSimilarPosts()
})

// Start
onMounted(() => {
  if (userStore.user?.id) boardStore.getBoards(userStore.user.id)
})

watch(
  () => route.params.postId,
  async (newPostId) => {
    window.scrollTo(0, 0)
    postId.value = Number(newPostId)
    outfitsOffset.value = 0
    postsOffset.value = 0
    endPosts.value = false
    outfits.value = []
    posts.value = []
    resetSimilarPostsDistribution()
    post.value = await getPost(postId.value)
    outfits.value = await getOutfitsWithItem(postId.value, outfitsOffset.value, outfitsLimit.value)
    posts.value = await getSimilarPosts(postId.value, postsOffset.value, postsLimit.value)
    postsOffset.value = posts.value.length
  },
  { immediate: true },
)

watch(
  () => boardStore.boards.length,
  () => {
    if (!post.value) return

    const newBoards = boardStore.boards.filter((board) => !saveBoardIds.value.includes(board.id))

    newBoards.forEach((board) => {
      const postInBoard = board.images.some(
        (img: any) => img.id === (post.value as any)?.main_image?.id,
      )
      if (postInBoard && !saveBoardIds.value.includes(board.id)) {
        saveBoardIds.value.push(board.id)
      }
    })
  },
)

watch(
  () => post.value,
  async (newPost) => {
    imagePreviewIdx.value = 0
    if ((newPost as any)?.saved_board_ids) {
      saveBoardIds.value = [...(newPost as any).saved_board_ids]
    }
  },
  { immediate: true },
)

const handleToggleItem = (postId: number) => {
  toggleItem(postId)
}
</script>

<template>
  <MainLayout>
    <Header />

    <div class="hidden md:block fixed top-30 left-[calc(var(--w-navbar)+1rem)]">
      <ArrowBack />
    </div>

    <!-- Контент -->
    <div ref="postsLayout" class="flex gap-4 px-1 md:pl-25 md:pt-10 md:pr-30">
      <div class="flex flex-col flex-1 min-w-0">
        <!-- Пост -->
        <div class="relative flex flex-wrap md:flex-nowrap gap-2 w-full md:gap-8 md:pr-21">
          <!-- Images -->
          <div
            class="relative flex w-full md:w-auto"
            :class="post?.images && post.images.length > 1 ? 'md:max-h-210' : ''"
          >
            <!-- All images -->
            <div
              v-if="post && post.images?.length > 1"
              :key="`thumb-${post.id}`"
              class="hidden absolute md:flex max-h-full"
            >
              <div class="relative max-h-full">
                <Swiper
                  :modules="swiperModules"
                  direction="vertical"
                  :slides-per-view="'auto'"
                  :space-between="12"
                  :navigation="{
                    nextEl: '.thumbnail-swiper-button-next',
                    prevEl: '.thumbnail-swiper-button-prev',
                  }"
                  @swiper="onThumbnailSwiper"
                  class="h-full"
                >
                  <SwiperSlide v-for="(image, index) in post?.images" :key="index" class="!h-auto">
                    <div
                      @click="() => selectImage(index)"
                      class="max-w-26 aspect-3/4 cursor-pointer"
                    >
                      <img
                        :src="image.path"
                        :alt="post?.title"
                        class="img-cover rounded-xl"
                        :class="
                          index == imagePreviewIdx
                            ? 'outline-2 outline-(--color-brend-simple) -outline-offset-2'
                            : ''
                        "
                      />
                    </div>
                  </SwiperSlide>
                </Swiper>

                <!-- Вверх -->
                <div
                  class="thumbnail-swiper-button-prev btn-default absolute top-2 left-1/2 bg-(--color-main-panel)/30 min-w-2! h-6! px-2! hover:bg-(--color-main-panel)! -translate-x-1/2 shadow-(--shadow-base) z-10 cursor-pointer"
                >
                  <IconChevronLeft class="rotate-90" />
                </div>

                <!-- Вниз -->
                <div
                  class="thumbnail-swiper-button-next btn-default absolute bottom-2 left-1/2 bg-(--color-main-panel)/30 min-w-2! h-6! px-2! hover:bg-(--color-main-panel)! -translate-x-1/2 shadow-(--shadow-base) z-10 cursor-pointer"
                >
                  <IconChevronRight class="rotate-90" />
                </div>
              </div>
            </div>
            <div
              v-if="post?.images"
              :key="`main-${post.id}`"
              class="relative flex flex-col justify-center items-center w-full md:w-120 h-full border border-(--color-input) rounded-2xl md:p-2"
              :class="post.images?.length > 1 ? 'md:ml-32' : ''"
            >
              <Swiper
                :modules="swiperModules"
                :slides-per-view="1"
                :navigation="{
                  nextEl: '.main-swiper-button-next',
                  prevEl: '.main-swiper-button-prev',
                }"
                @swiper="onMainSwiper"
                @slide-change="onMainSlideChange"
                class="w-full h-full"
              >
                <SwiperSlide
                  v-for="(image, index) in post?.images"
                  :key="index"
                  class="flex h-full"
                >
                  <img
                    @click="openImage(index)"
                    :src="image.path"
                    class="max-w-full max-h-full object-contain rounded-xl m-auto cursor-pointer"
                    :alt="post?.title"
                  />
                </SwiperSlide>
              </Swiper>

              <div
                @click="router.back()"
                class="btn-default absolute z-10 md:hidden! top-8 left-0 bg-(--color-main-panel)/30 hover:bg-(--color-main-panel)! translate-x-2 -translate-y-1/2 shadow-(--shadow-base) cursor-pointer"
              >
                <IconArrowBack width="28" class="transition" />
              </div>

              <div
                v-if="post.images?.length > 1"
                class="main-swiper-button-prev btn-default absolute z-10 top-1/2 left-0 md:left-1 bg-(--color-main-panel)/30 min-w-2! px-2! hover:bg-(--color-main-panel)! translate-x-2 -translate-y-1/2 shadow-(--shadow-base) cursor-pointer"
              >
                <IconChevronLeft />
              </div>

              <div
                v-if="post.images?.length > 1"
                class="main-swiper-button-next btn-default absolute z-10 top-1/2 right-0 md:right-1 bg-(--color-main-panel)/30 min-w-2! px-2! hover:bg-(--color-main-panel)! -translate-x-2 -translate-y-1/2 shadow-(--shadow-base) cursor-pointer"
              >
                <IconChevronRight />
              </div>
            </div>
          </div>

          <!-- Post info -->
          <div class="flex flex-col w-full min-w-0 md:w-auto px-4 md:px-auto">
            <div class="flex justify-between items-center">
              <router-link
                v-if="post?.author"
                :to="{ name: 'profile', params: { userId: post?.author.id } }"
                class="btn-fit gap-2 w-fit p-2 pr-3 -ml-2"
              >
                <div>
                  <img
                    v-if="post?.author.avatar_path"
                    :src="post?.author.avatar_path"
                    :alt="post?.author.login"
                    class="img-cover w-10! aspect-square rounded-full"
                  />

                  <div
                    v-else
                    class="flex justify-center items-center w-8 bg-(--color-input) p-2 rounded-full"
                  >
                    <IconAvatar width="24" class="stroke-(--color-hover-dark)" />
                  </div>
                </div>
                <span class="text[16px] md:text-[18px]">{{ post?.author.login }}</span>
              </router-link>

              <div class="flex gap-1">
                <Share />
                <button
                  v-if="post?.author.id === userStore.user?.id"
                  @click="() => (isDeletePostModal = true)"
                  class="btn-default h-10! border border-(--color-border)"
                >
                  <IconTrash width="22" />
                </button>
              </div>
            </div>

            <h1 class="font-[ComicRelief] text-2xl md:text-3xl mt-2 md:mt-8">{{ post?.title }}</h1>

            <span v-if="post?.description" class="md:text-base mt-2 md:mt-6">{{
              post?.description
            }}</span>

            <!-- Теги -->
            <div class="flex flex-wrap gap-2 mt-6">
              <div
                v-for="tag in post?.tags"
                :key="tag.id"
                class="flex items-center gap-1 bg-(--color-brend-light) border border-(--color-brend-simple) text-[12px] md:text-[14px] text-(--color-brend-simple) py-0.5 px-3 rounded-4xl cursor-pointer"
              >
                <span>#{{ tag.title }}</span>
              </div>
            </div>

            <!-- Ссылки -->
            <span v-if="post?.links?.length" class="font-[ComicRelief] text-[20px] mt-8"
              >Где купить</span
            >

            <div
              v-if="post?.links?.length"
              class="flex flex-col w-full border border-(--color-border) rounded-xl mt-3"
            >
              <div
                v-for="(link, index) in post?.links"
                :key="index"
                class="flex justify-between items-center gap-4 py-3 mx-5"
                :class="index !== post?.links.length - 1 ? 'border-b border-(--color-border)' : ''"
              >
                <div class="flex gap-3 items-center">
                  <IconLink width="20" class="stroke-(--color-text)" />
                  <a
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-[15px] hover:text-(--color-brend-simple) hover:underline transition"
                    >{{ formatLink(link.url) }}</a
                  >
                </div>
                <button
                  :href="link.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn-default h-auto! text-(--color-brend-simple) text-nowrap hover:bg-(--color-brend-light) py-2! border border-(--color-brend-simple) rounded-lg! transition"
                >
                  В магазин
                </button>
              </div>
            </div>

            <!-- Вещи в образе -->
            <span v-if="post?.items?.length" class="font-[ComicRelief] text-[20px] mt-4 md:mt-8"
              >Вещи в образе</span
            >

            <div
              v-if="post?.items?.length"
              class="flex flex-col w-full border border-(--color-border) rounded-xl mt-3"
            >
              <router-link
                v-for="(item, index) in post?.items"
                :key="item.id"
                :to="{ name: 'post', params: { postId: item.id } }"
                class="flex items-center gap-4 min-w-0 py-3 mx-5 hover:opacity-70 transition"
                :class="index !== post?.items.length - 1 ? 'border-b border-(--color-border)' : ''"
              >
                <div class="flex flex-1 items-center gap-3 min-w-0 overflow-hidden">
                  <img
                    :src="item.main_image.path_preview"
                    :alt="item.title"
                    class="w-16! shrink-0 aspect-square img-cover rounded-lg"
                  />

                  <div class="flex flex-col flex-1 min-w-0">
                    <h4 class="font-medium truncate">{{ item.title }}</h4>
                    <p class="text-sm text-(--color-text-second) truncate">
                      {{ item.description }}
                    </p>
                  </div>

                  <button
                    target="_blank"
                    rel="noopener noreferrer"
                    class="shrink-0 btn-default min-w-10 min-h-10! md:w-auto md:h-auto! text-(--color-brend-simple) text-nowrap hover:bg-(--color-brend-light) p-0! md:p-2! border border-(--color-brend-simple) rounded-lg! transition"
                  >
                    <IconOpen v-if="isMobile" width="20" />
                    <span v-else> Просмотр</span>
                  </button>
                </div>
              </router-link>
            </div>

            <div class="flex flex-wrap gap-2 pt-6 md:pt-10">
              <!-- Like -->
              <button
                v-if="post"
                @click.prevent="() => like(post!.id)"
                class="shrink-0 btn-default gap-2! w-fit text-md border border-(--color-border)"
              >
                <IconHeart
                  width="20"
                  stroke-width="1.7"
                  class="opacity-70 transition"
                  :class="post.is_liked ? 'fill-rose-500 stroke-rose-500' : ''"
                />
                <span class="mt-0.5">
                  {{ post?.likes_count }}
                </span>
              </button>

              <!-- Save -->
              <div class="relative">
                <button
                  @click.prevent="toggleSaveModal"
                  ref="save"
                  class="btn-default border border-(--color-border)"
                >
                  <IconFavorite width="19" class="shrink-0" />
                  Сохранить
                </button>

                <!-- Save modal -->
                <ModalSavePost
                  v-if="isSavePost"
                  :boards="boardStore.boards"
                  :post-id="post?.id || 0"
                  :is-last-column="isLastColumn"
                  v-model:save-board-ids="saveBoardIds"
                  @save-post="savePost"
                  ref="saveModal"
                />
              </div>

              <!-- Add to generate -->
              <button
                v-if="post"
                @click.prevent="() => handleToggleItem(post!.id)"
                class="gap-2!"
                :class="
                  isItemAdded(post!.id)
                    ? 'btn-dark min-w-0'
                    : 'btn-default border border-(--color-border)'
                "
              >
                <IconSuccess v-if="isItemAdded(post!.id)" width="24" class="shrink-0" />
                <IconBrush v-else width="20" class="shrink-0" />
                {{ isItemAdded(post!.id) ? 'Добавлено' : 'В образ' }}
              </button>

              <!-- Примерить -->
              <router-link
                v-if="post && isItemAdded(post.id)"
                :to="{ name: 'generate' }"
                class="btn-accent gap-2!"
              >
                <IconBrush width="20" class="shrink-0" />
                Примерить
              </router-link>
            </div>
          </div>
          <LoaderCircle v-if="isLoadPost" class="z-10" />
        </div>

        <!-- Образы + похожие снизу + похожие справа -->
        <div class="flex flex-1 gap-0 md:gap-8 max-w-full mt-6 md:mt-14">
          <!-- Образы + похожие снизу -->
          <div class="overflow-hidden flex flex-1 flex-col gap-8">
            <!-- Образ -->
            <div
              v-if="outfits.length"
              :key="`outfits-${post?.id}`"
              class="relative flex flex-col gap-3 md:gap-7 flex-1 max-h-fit px-4 md:px-7 py-2 md:py-8 border border-(--color-input) rounded-2xl"
            >
              <div class="flex justify-between items-center">
                <span class="font-[ComicRelief] text-xl">Образы с этой вещью</span>
                <span class="text-[16px] text-(--color-text-second)"
                  >{{ outfits.length }} образы</span
                >
              </div>
              <div class="relative">
                <Swiper
                  :modules="swiperModules"
                  :slides-per-view="'auto'"
                  :space-between="32"
                  :centered-slides="true"
                  :effect="'coverflow'"
                  :coverflow-effect="{
                    rotate: 0,
                    stretch: 0,
                    depth: 100,
                    modifier: 2,
                    slideShadows: false,
                  }"
                  :navigation="{
                    nextEl: '.outfit-swiper-button-next',
                    prevEl: '.outfit-swiper-button-prev',
                  }"
                  @swiper="onOutfitSwiper"
                  class="pb-4 outfit-swiper"
                >
                  <SwiperSlide v-for="outfit in outfits" :key="outfit.id" class="!w-auto">
                    <router-link
                      :to="{ name: 'post', params: { postId: outfit.id } }"
                      class="overflow-hidden aspect-3/5 h-90 md:h-100 bg-(--color-brend-bg) rounded-2xl shrink-0 block"
                    >
                      <img
                        :src="outfit.main_image.path_preview"
                        :alt="outfit.title"
                        class="img-cover"
                      />
                    </router-link>
                  </SwiperSlide>
                </Swiper>
                <div
                  class="outfit-swiper-button-prev btn-default absolute top-1/2 left-0 bg-(--color-main-panel) min-w-2! px-2! hover:bg-(--color-main-panel)! translate-x-2 -translate-y-1/2 shadow-(--shadow-base) opacity-80 hover:opacity-100 z-10 cursor-pointer"
                >
                  <IconChevronLeft />
                </div>
                <div
                  class="outfit-swiper-button-next btn-default absolute top-1/2 right-0 bg-(--color-main-panel) min-w-2! px-2! hover:bg-(--color-main-panel)! -translate-x-2 -translate-y-1/2 shadow-(--shadow-base) opacity-80 hover:opacity-100 z-10 cursor-pointer"
                >
                  <IconChevronRight />
                </div>
              </div>
            </div>
            <PostsTile v-if="!isLoadPost" :posts="postsLeft" :countColumns="countPostsInRowLeft" />
          </div>
        </div>
      </div>

      <PostsTile
        v-if="!isMobile && isRightColumn && !isLoadPost"
        :key="post?.id"
        :posts="postsRight"
        :countColumns="countPostsInRowRigth"
        :style="{ width: `${rightPostsTileWidth}px` }"
        class="shrink-0"
      />
    </div>
  </MainLayout>

  <ModalCreateBoard />
  <ModalDeletePost
    :is-open="isDeletePostModal"
    :is-load="isLoadDeletePost"
    @close="() => (isDeletePostModal = false)"
    @confirm="confirmDeletePost"
  />
</template>

<style scoped>
.swiper-slide {
  display: flex !important;
  align-items: center !important;
}

.outfit-swiper .swiper-slide,
.outfit-swiper .swiper-slide img {
  transition: all 0.3s ease;
}

.outfit-swiper .swiper-slide:not(.swiper-slide-active) {
  transform: scale(0.85);
}

.outfit-swiper .swiper-slide:not(.swiper-slide-active) img {
  opacity: 0.5;
}

.outfit-swiper .swiper-slide-active {
  transform: scale(1);
}

.outfit-swiper .swiper-slide-active img {
  opacity: 1;
}
</style>
