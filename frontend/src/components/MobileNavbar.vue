<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import IconAdd from './icons/IconAdd.vue'
import IconBrush from './icons/IconBrush.vue'
import IconHome from './icons/IconHome.vue'

// Scroll tracking
const scrollY = ref(0)
const navbarHidden = ref(false)
let lastScrollY = 0

const updateScroll = () => {
  scrollY.value = window.scrollY

  if (scrollY.value > 100 && scrollY.value > lastScrollY + 20) {
    navbarHidden.value = true
    lastScrollY = scrollY.value
  }

  if (scrollY.value < lastScrollY) {
    navbarHidden.value = false
    lastScrollY = scrollY.value
  }
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
    class="fixed z-100 left-1/2 bottom-4 flex gap-3 -translate-x-1/2 transition-[bottom] duration-400"
    :class="navbarHidden ? '-bottom-12!' : ''"
  >
    <router-link to="/" class="btn-icon-accent-hover bg-(--color-main-panel-dark)">
      <IconHome width="22" stroke-width="1.6" />
    </router-link>

    <!-- Create -->
    <router-link to="/generate" class="btn-icon-accent-hover bg-(--color-main-panel-dark)">
      <IconBrush width="22" stroke-width="1.6" />
    </router-link>

    <router-link
      :to="{ name: 'public' }"
      class="btn-icon-accent-hover bg-(--color-main-panel-dark)"
    >
      <IconAdd width="22" />
    </router-link>
  </div>
</template>
