<script setup lang="ts">
import { ref, useTemplateRef } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { useRouter } from 'vue-router'
import { authApi } from '@/api/api'
import { useUserStore } from '@/stores/user.stores'
import IconAvatar from '@/components/icons/IconAvatar.vue'
import IconPhoto from '@/components/icons/IconPhoto.vue'
import IconWallet from './icons/IconWallet.vue'

const router = useRouter()
const userStore = useUserStore()

const isOpenUserModal = ref<boolean>(false)
const userModalRef = useTemplateRef<HTMLElement>('userModal')
const profileRef = useTemplateRef<HTMLElement>('profile')

onClickOutside(
  userModalRef,
  () => {
    isOpenUserModal.value = false
  },
  {
    ignore: [profileRef],
  },
)

const logout = async () => {
  await authApi.logout()
  userStore.clearUser()
  localStorage.removeItem('token')
  await router.push({ name: 'home' })
}
</script>

<template>
  <div v-if="userStore.isAccount" class="relative">
    <div
      @click.stop="isOpenUserModal = !isOpenUserModal"
      ref="profile"
      class="btn-icon"
    >
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
              class="btn-accent flex items-center gap-1! h-6! text-[11px]"
            >
              <IconWallet width="15" class="s shrink-0" />
              <span class="mt-0.5">Пополнить</span>
            </button>
          </div>
        </div>
      </router-link>

      <!-- Actions -->
      <span class="block text-sm text-(--color-text-second) pt-4 px-3 pb-2"
        >Действия</span
      >
      <button class="btn-list font-medium">Настройки</button>
      <button @click="logout()" class="btn-list font-medium">Выход</button>
    </div>
  </div>
</template>
