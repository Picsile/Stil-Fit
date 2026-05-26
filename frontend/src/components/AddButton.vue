<script setup lang="ts">
import { ref, useTemplateRef } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { useUserStore } from '@/stores/user.stores'
import IconAdd from '@/components/icons/IconAdd.vue'

const userStore = useUserStore()

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
</script>

<template>
  <div v-if="userStore.isAccount" class="relative">
    <div
      @click.stop="isOpenAddModal = !isOpenAddModal"
      ref="add"
      class="btn-icon"
    >
      <IconAdd width="26" />
    </div>

    <!-- Add post modal -->
    <div
      v-if="isOpenAddModal"
      ref="addModal"
      class="absolute top-12 right-0 z-100 bg-(--color-main-panel) p-3 rounded-2xl shadow-(--shadow-modal)"
    >
      <router-link
        :to="{ name: 'public' }"
        class="btn-list font-medium"
      >
        <span>Опубликовать</span>
      </router-link>
    </div>
  </div>
</template>
