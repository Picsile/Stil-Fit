<script setup lang="ts">
import IconHeart from '@/components/icons/IconHeart.vue'
import IconAvatar from '@/components/icons/IconAvatar.vue'
import IconSuccess from '@/components/icons/IconSuccess.vue'
import type { PostPreviewData } from '@/types/view.types'
import { accountApi } from '@/api/api'
import { ref, useTemplateRef, watch } from 'vue'
import type { BoardId } from '@/types/board.types'
import { onClickOutside } from '@vueuse/core'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import { useGenerationItems } from '@/composables/useGenerationItems'
import { useAuthRedirect } from '@/composables/useAuthRedirect'
import ModalSavePost from '@/components/modals/ModalSavePost.vue'
import IconEllipsis from '@/components/icons/IconEllipsis.vue'
import IconFavorite from '@/components/icons/IconFavorite.vue'
import { useIsMobile } from '@/composables/useIsMobile'

const { isMobile } = useIsMobile()

const userStore = useUserStore()
const boardStore = useBoardStore()
const { isItemAdded, toggleItem } = useGenerationItems()
const { requireAuth } = useAuthRedirect()

interface Props {
  post: PostPreviewData
}

const props = defineProps<Props>()

// Like
const like = async (postId: number) => {
  if (!requireAuth()) return

  try {
    if (props.post.is_liked) {
      props.post.is_liked = false
      props.post.likes_count--
    } else {
      props.post.is_liked = true
      props.post.likes_count++
    }

    const data = await accountApi.like(postId)

    if (data?.status === 'error') {
      throw new Error(data?.message)
    }
    console.log(data)
  } catch (e) {
    console.error('Ошибка при добавлении лайка: ', e)
  }
}

// Save in board
const isSavePost = ref<boolean>(false)
const saveBoardIds = ref<BoardId[]>(props.post.saved_board_ids || [])
const saveModalRef = useTemplateRef<HTMLElement>('saveModal')
const saveRef = useTemplateRef<HTMLElement>('save')
const isLastColumn = ref<boolean>(false)

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

const savePost = async (boardId: BoardId, postId: number) => {
  try {
    const index = saveBoardIds.value.findIndex((id) => id === boardId)

    if (index === -1) {
      saveBoardIds.value.push(boardId)
    } else {
      saveBoardIds.value.splice(index, 1)
    }

    const data = await accountApi.savePost(boardId, postId)
    console.log(data)

    if (data?.status === 'error') {
      saveBoardIds.value.splice(index, 1)
      throw new Error(data?.message)
    }
  } catch (e) {
    console.error('Ошибка при сохранении поста: ', e)
  }
}

const updateSavedBoards = () => {
  const newBoards = boardStore.boards.filter((board) => !saveBoardIds.value.includes(board.id))

  newBoards.forEach((board) => {
    const postInBoard = board.images.some((img) => img.id === props.post.main_image.id)
    if (postInBoard && !saveBoardIds.value.includes(board.id)) {
      saveBoardIds.value.push(board.id)
    }
  })
}

watch(() => boardStore.boards.length, updateSavedBoards)

const handleToggleItem = (postId: number) => {
  toggleItem(postId)
}
</script>

<template>
  <!-- Image -->
  <div class="relative" :class="isSavePost ? 'z-30' : 'z-20'">
    <router-link
      :to="{ name: 'post', params: { postId: post.id } }"
      class="group block relative rounded-2xl shadow-(--shadow-base)"
    >
      <img
        :src="post.main_image.path_preview"
        :width="post.main_image.width"
        :height="post.main_image.height"
        :alt="post.title"
        class="img-cover group-hover:brightness-80 rounded-2xl transition duration-200"
      />

      <!-- Кнопки -->
      <div
        class="hidden md:flex absolute z-40 top-0 left-0 flex-col justify-between w-full h-full p-1.5 opacity-0 group-hover:opacity-100 transition"
      >
        <div class="flex gap-1">
          <!-- Author -->
          <router-link
            :to="{ name: 'profile', params: { userId: post.author.id } }"
            class="btn-opacity-dark justify-start! gap-1.5 h-10! px-3 min-w-0 shrink"
            :class="userStore.isAccount ? 'flex-1' : 'w-fit'"
          >
            <div
              class="flex-none overflow-hidden flex justify-center items-center w-6 h-6 rounded-full"
              :class="!post.author.avatar_path ? 'bg-(--color-input)' : ''"
            >
              <img
                v-if="post.author.avatar_path"
                :src="post.author.avatar_path"
                class="img-cover"
              />
              <IconAvatar
                v-else
                width="18"
                stroke-width="1.75"
                class="stroke-(--color-text-second)"
              />
            </div>

            <!-- Текст с truncate -->
            <span class="truncate">
              {{ post.author.login }}
            </span>
          </router-link>

          <!-- Save -->
          <button
            @click.prevent="toggleSaveModal"
            ref="save"
            class="w-26! h-10! transition"
            :class="saveBoardIds.length > 0 ? 'btn-opacity-dark' : 'btn-accent'"
          >
            {{ saveBoardIds.length > 0 ? 'Сохранено' : 'Сохранить' }}
          </button>
        </div>

        <div class="flex gap-1">
          <!-- Like -->
          <button
            @click.prevent="() => like(post.id)"
            class="shrink-0 btn-default gap-1! w-fit h-10! bg-(--color-main-panel) font-[ComicRelief] px-3!"
          >
            <IconHeart
              width="20"
              stroke-width="1.7"
              class="opacity-70 transition"
              :class="post.is_liked ? 'fill-rose-500 stroke-rose-500' : ''"
            />
            {{ post.likes_count }}
          </button>

          <!-- Add to generate -->
          <button
            @click.prevent="() => handleToggleItem(post.id)"
            class="w-full h-10!"
            :class="
              isItemAdded(post.id) ? 'btn-opacity-dark' : 'btn-default bg-(--color-main-panel)'
            "
          >
            <span class="truncate">{{
              isItemAdded(post.id) ? 'Добавлено' : 'В образ'
            }}</span>
          </button>
        </div>
      </div>
    </router-link>

    <button
      v-if="isMobile"
      @click.prevent="() => like(post.id)"
      class="btn-default gap-1.5! w-fit h-10! hover:bg-transparent! font-[ComicRelief] shrink-0 px-3!"
    >
      <IconHeart
        width="20"
        stroke-width="1.7"
        class="opacity-70 transition"
        :class="post.is_liked ? 'fill-rose-500 stroke-rose-500' : ''"
      />
      {{ post.likes_count }}
    </button>

    <!-- Save modal -->
    <ModalSavePost
      v-if="isSavePost"
      :boards="boardStore.boards"
      :post-id="post.id"
      :is-last-column="isLastColumn"
      v-model:save-board-ids="saveBoardIds"
      @save-post="savePost"
      ref="saveModal"
    />
  </div>
</template>
