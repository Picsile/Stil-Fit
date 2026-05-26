<script setup lang="ts">
import IconHeart from '@/components/icons/IconHeart.vue'
import IconAvatar from '@/components/icons/IconAvatar.vue'
import IconSuccess from '@/components/icons/IconSuccess.vue'
import type { PostPreviewData } from '@/types/view.types'
import { accountApi } from '@/api/api'
import { ref, useTemplateRef } from 'vue'
import type { BoardId } from '@/types/board.types'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import { useGenerationItems } from '@/composables/useGenerationItems'
import { useAuthRedirect } from '@/composables/useAuthRedirect'
import ModalSavePost from '@/components/modals/ModalSavePost.vue'
import { onClickOutside } from '@vueuse/core'
import { useRoute } from 'vue-router'

const userStore = useUserStore()
const boardStore = useBoardStore()
const route = useRoute()
const { isItemAdded, toggleItem } = useGenerationItems()
const { requireAuth } = useAuthRedirect()

interface Props {
  post: PostPreviewData
}

const props = defineProps<Props>()

const boardId = Number(route.params.boardId)

// Remove / Save in board
const isSavePost = ref<boolean>(false)
const isSavedPost = ref<boolean>(true)

const saveBoardIds = ref<BoardId[]>([])
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

const unSavePost = async (boardId: BoardId) => {
  if (!requireAuth()) return

  try {
    const data = await accountApi.savePost(boardId, props.post.id)
    if (data?.status === 'success') {
      isSavedPost.value = false
    } else {
      isSavedPost.value = true
      throw new Error(data?.message)
    }
  } catch (e) {
    console.error('Ошибка при удалении поста: ', e)
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
  if (!requireAuth()) return

  try {
    const index = saveBoardIds.value.findIndex((id) => id === boardId)

    if (index === -1) {
      saveBoardIds.value.push(boardId)
    } else {
      saveBoardIds.value.splice(index, 1)
    }

    const data = await accountApi.savePost(boardId, postId)

    if (data?.status === 'error') {
      saveBoardIds.value.splice(index, 1)
      throw new Error(data?.message)
    }
  } catch (e) {
    console.error('Ошибка при сохранении поста: ', e)
  }
}

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

const handleToggleItem = (postId: number) => {
  toggleItem(postId)
}
</script>

<template>
    <!-- Image -->
    <router-link
      :to="{ name: 'post', params: { postId: post.id } }"
      class="group relative block"
      :class="isSavePost ? 'z-30' : 'z-20'"
    >
      <img
        :src="post.main_image.path_preview"
        :width="post.main_image.width"
        :height="post.main_image.height"
        :alt="post.title"
        class="img-cover group-hover:brightness-80 rounded-2xl"
      />

      <!-- Кнопки -->
      <div
        class="hidden group-hover:flex absolute z-40 top-0 left-0 flex-col justify-between w-full h-full p-2"
      >
        <div class="flex justify-end gap-1.5">
          <!-- Delete / Save -->
          <button
            v-if="isSavedPost"
            @click.prevent="() => unSavePost(boardId)"
            class="btn-dark h-10! text-white px-4"
          >
            Сохранено
          </button>
          <button
            v-else
            @click.prevent="toggleSaveModal"
            ref="save"
            class="btn-accent shrink-0 min-w-fit w-fit h-10! bg-(--color-main-panel)"
          >
            Сохранить
          </button>
        </div>

        <div class="flex gap-1.5">
          <!-- Like -->
          <button
            @click.prevent="() => like(post.id)"
            class="shrink-0 btn-default gap-1! w-fit h-10! bg-(--color-main-panel) text-md px-3!"
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
            :class="isItemAdded(post.id) ? 'btn-opacity-dark' : 'btn-default bg-(--color-main-panel)'"
          >
            <IconSuccess v-if="isItemAdded(post.id)" width="20" />
            <span class="truncate">{{ isItemAdded(post.id) ? 'Добавлено' : 'В образ' }}</span>
          </button>
        </div>
      </div>

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
    </router-link>
</template>
