<script setup lang="ts">
import IconBrush from './icons/IconBrush.vue'
import IconHome from './icons/IconHome.vue'
</script>

<template>
  <nav
    class="fixed z-100 top-0 left-0 flex flex-col justify-between items-center w-(--w-navbar) h-screen bg-(--color-main-panel) py-4 border-r border-r-(--color-input)"
  >
    <div class="flex flex-col gap-7">
      <!-- Logo -->
      <router-link to="/" class="btn-icon-accent-hover">
        <img
          class="rounded-full"
          src="/static/logo.svg"
          width="30"
          alt="Главная"
        />
      </router-link>

      <!-- Home -->
      <router-link to="/" class="btn-icon-accent-hover">
        <IconHome width="24" stroke-width="1.6" />
      </router-link>

      <!-- Create -->
      <router-link to="/generate" class="btn-icon-accent-hover">
        <IconBrush width="24" stroke-width="1.6" />
      </router-link>
    </div>

    <!-- <router-link to="/" class="btn-icon-accent-hover">
      <IconSettings width="24" stroke-width="1.6" />
    </router-link> -->
  </nav>
</template>

<style scoped>
.btn-icon:hover {
  animation: shake 1s ease-out infinite;
  animation-delay: 1s;
}

/* Anim */
@keyframes shake {
  0%,
  40% {
    transform: rotate(0deg);
  }
  45% {
    transform: rotate(-10deg);
  }
  50% {
    transform: rotate(10deg);
  }
  55% {
    transform: rotate(-10deg);
  }
  60%,
  100% {
    transform: rotate(0deg);
  }
}
</style>
