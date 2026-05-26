<script setup lang="ts">
import ModalOpenImage from '@/components/modals/ModalOpenImage.vue'
import AppDesktopLayout from './layouts/AppDesktopLayout.vue'
import Navbar from './components/Navbar.vue'
import MobileNavbar from './components/MobileNavbar.vue'
import AppMobileLayout from './layouts/AppMobileLayout.vue'
import { onMounted, onUnmounted, ref } from 'vue'

const isMobile = ref(false)

const checkWidth = () => {
  isMobile.value = window.innerWidth < 640
}

onMounted(() => {
  checkWidth()
  window.addEventListener('resize', checkWidth)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkWidth)
})
</script>

<template>
  <template v-if="isMobile">
    <MobileNavbar v-if="!$route.meta.hideNavbar" />
    <AppMobileLayout>
      <router-view />
    </AppMobileLayout>
  </template>

  <template v-else>
    <Navbar v-if="!$route.meta.hideNavbar" />
    <AppDesktopLayout :class="!$route.meta.hideNavbar ? 'pl-(--w-navbar)' : ''">
      <router-view />
    </AppDesktopLayout>
  </template>

  <ModalOpenImage />
</template>
