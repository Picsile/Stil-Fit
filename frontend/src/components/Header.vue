<script setup lang="ts">
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue'
import { onClickOutside } from '@vueuse/core'
import IconUser from './icons/IconUser.vue'
import IconAdd from './icons/IconAdd.vue'
import IconPhoto from './icons/IconPhoto.vue'
import { authApi } from '@/api/api'
import { useIsMobile } from '@/composables/useIsMobile'
import InputSearch from './forms/InputSearch.vue'
import BoardListTitle from '../modules/board/components/BoardsListTitle.vue'
import { useUserStore } from '@/stores/user.stores'
import { useGenerationDataStore } from '@/stores/generationData.stores'
import type { BoardId, BoardTitleData } from '@/types/board.types'
import { useRouter } from 'vue-router'
import IconAvatar from './icons/IconAvatar.vue'
import IconWallet from './icons/IconWallet.vue'

// Состояния
const router = useRouter()
const userStore = useUserStore()
const { isMobile } = useIsMobile()
const generationDataStore = useGenerationDataStore()

interface Props {
  boards?: BoardTitleData[] | []
  showSelectionPanel?: boolean
  hideBoards?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showSelectionPanel: false,
  hideBoards: false,
})

const activeBoardId = defineModel<BoardId>('active-board-id')

// Boards

// Scroll tracking
const scrollY = ref(0)
const boardsHidden = ref(false)
let lastScrollY = 0

const updateScroll = () => {
  scrollY.value = window.scrollY

  if (scrollY.value > 100 && scrollY.value > lastScrollY + 20) {
    boardsHidden.value = true
    lastScrollY = scrollY.value
  }

  if (scrollY.value < lastScrollY) {
    boardsHidden.value = false
    lastScrollY = scrollY.value
  }
}

// User modal
const isOpenUserModal = ref<boolean>(false)
const userModalRef = useTemplateRef<HTMLElement>('userModal')
const profileRef = useTemplateRef<HTMLElement>('profile')

onClickOutside(
  userModalRef,
  () => {
    isOpenUserModal.value = false
    isOpenAddModal.value = false
  },
  {
    ignore: [profileRef],
  },
)

// Add modal
const isOpenAddModal = ref(false)
const addModalRef = useTemplateRef<HTMLElement>('addModal')
const addRef = useTemplateRef<HTMLElement>('add')

onClickOutside(
  addModalRef,
  () => {
    isOpenAddModal.value = false
  },
  {
    ignore: [addRef],
  },
)

const logout = async () => {
  await authApi.logout()
  userStore.clearUser()
  localStorage.removeItem('token')

  await router.push({ name: 'home' })
}

const goToBuyCoin = async () => {
  isOpenUserModal.value = false
  await router.push({ name: 'buy-coin' })
}

// Start
onMounted(() => {
  window.addEventListener('scroll', updateScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateScroll)
})
</script>

<template>
  <div
    class="fixed z-75 top-0 left-0 md:left-(--w-navbar) right-0 bg-(--color-main-panel) rounded-b-xl transition-[top] duration-400"
    :class="isMobile && boardsHidden && '-top-16!'"
  >
    <!-- Main content -->
    <div
      class="absolute z-50 top-0 flex items-center w-full bg-(--color-main-panel) p-2 px-1 md:p-4"
    >
      <router-link to="/" class="md:hidden! btn-icon h-12! aspect-square mr-1">
        <img class="rounded-full" src="/static/logo.svg" width="30" alt="Главная" />
      </router-link>

      <!-- Input search -->
      <InputSearch class="mr-1 md:mr-2" />

      <!-- User -->
      <div v-if="userStore.isAccount" class="relative">
        <div @click.stop="isOpenUserModal = !isOpenUserModal" ref="profile" class="btn-icon h-12!">
          <img
            v-if="userStore.user?.avatar_path"
            :src="userStore.user?.avatar_path"
            :alt="userStore.user.login"
            class="img-cover w-8! h-8! rounded-full"
          />

          <div
            v-else
            class="flex justify-center items-center w-8 bg-(--color-input) p-2 rounded-full"
          >
            <IconAvatar width="24" class="stroke-(--color-hover-dark)" />
          </div>
        </div>

        <!-- User modal -->
        <div
          v-if="isOpenUserModal"
          ref="userModal"
          class="absolute top-12 right-0 z-100 bg-(--color-main-panel) p-3 rounded-2xl shadow-(--shadow-modal)"
        >
          <!-- User info -->
          <router-link :to="{ name: 'my-profile' }" class="btn-fit gap-3 p-3">
            <div
              class="flex justify-center items-center w-12 aspect-square bg-(--color-input) rounded-full"
              :class="!userStore.user?.avatar_path ? 'hover:brightness-90' : ''"
            >
              <img
                v-if="userStore.user?.avatar_path"
                :src="userStore.user?.avatar_path"
                :alt="userStore.user.login"
                class="img-cover rounded-full"
              />

              <IconPhoto v-else width="24" class="stroke-(--color-text-second)" />
            </div>

            <div>
              <h5>{{ userStore.user?.login }}</h5>
              <div class="flex justify-between items-center gap-5">
                <div class="flex items-center gap-1">
                  <img src="../../public/static/fitcoin.png" alt="" class="w-4" />
                  <span class="font-[ComicRelief] mt-0.5">{{
                    userStore.user?.quantity_fitcoins
                  }}</span>
                </div>

                <button
                  @click.prevent="goToBuyCoin"
                  class="btn-accent flex items-center gap-1! h-7! text-[11px]"
                >
                  <IconWallet width="15" class="s shrink-0" />
                  <span class="mt-0.5">Пополнить</span>
                </button>
              </div>
            </div>
          </router-link>

          <!-- Actions -->
          <span class="block text-sm text-(--color-text-second) pt-4 px-3 pb-2">Действия</span>
          <!-- <button class="btn-list font-medium">Настройки</button> -->
          <button @click="logout()" class="btn-list font-medium">Выход</button>
        </div>
      </div>

      <!-- Add post -->
      <div v-if="userStore.isAccount" class="hidden md:block relative">
        <div @click.stop="isOpenAddModal = !isOpenAddModal" ref="add" class="btn-icon h-12!">
          <IconAdd width="26" />
        </div>

        <!-- Add post modal -->
        <div
          v-if="isOpenAddModal"
          ref="addModal"
          class="absolute top-12 right-0 bg-(--color-main-panel) p-3 rounded-2xl shadow-(--shadow-modal)"
        >
          <router-link :to="{ name: 'public' }" class="btn-list font-medium">
            <span>Опубликовать</span>
          </router-link>
        </div>
      </div>

      <!-- Authorization -->
      <router-link v-if="!userStore.isAccount" :to="{ name: 'login' }" class="btn-accent shrink-0">
        <IconUser class="shrink-0 mb-0.5" width="20" stroke-width="1.75" />
        <span>Войти</span>
      </router-link>
    </div>

    <!-- Selection Panel -->
    <div
      v-if="showSelectionPanel && generationDataStore.selectedCount > 0"
      class="relative z-25 flex justify-between items-center bg-(--color-main-panel) px-3 md:px-7 pt-16 pb-2 md:pb-3 md:pt-20 border-t border-(--color-border) transition-all ease-in-out"
    >
      <div class="flex items-center gap-4">
        <span class="text-(--color-text-second)"
          >Выбрано: {{ generationDataStore.selectedCount }}</span
        >
        <button
          @click="generationDataStore.clearSelection()"
          class="text-(--color-text-second) hover:text-(--color-brend-simple) transition"
        >
          Очистить
        </button>
      </div>

      <router-link
        :to="{ name: 'public', query: { from: 'generation' } }"
        class="btn-dark h-8! text-[13px] px-3!"
        >Опубликовать</router-link
      >
    </div>

    <!-- Boards -->
    <div
      v-if="userStore.isAccount && !hideBoards"
      class="relative z-25 flex bg-(--color-main-panel) px-5 md:px-7 pb-3 md:pb-5 transition-all duration-300 ease-in-out"
      :class="boards?.length ? (isMobile ? 'pt-17' : boardsHidden ? 'pt-4' : 'pt-20') : ''"
    >
      <BoardListTitle v-if="boards?.length" :boards="boards" v-model:active-id="activeBoardId" />
    </div>
  </div>
</template>
