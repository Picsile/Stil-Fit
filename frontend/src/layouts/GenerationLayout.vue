<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useMediaQuery } from '@vueuse/core'
import { useGenerationDataStore } from '@/stores/generationData.stores'
import { useIsMobile } from '@/composables/useIsMobile'
import IconChevronLeft from '@/components/icons/IconChevronLeft.vue'
import IconBrush from '@/components/icons/IconBrush.vue'
import IconChevronRight from '@/components/icons/IconChevronRight.vue'

const widthPanel = defineModel<number>({ default: 0 })

const { isMobile } = useIsMobile()

const generationDataStore = useGenerationDataStore()
const isLg = useMediaQuery('(min-width: 1024px)')

const paddingTop = computed(() =>
  generationDataStore.selectedCount > 0 ? 'pt-23.5 md:pt-28' : 'pt-14 md:pt-20',
)

const contentMarginLeft = computed(() =>
  isLg.value ? `calc(1.25rem + ${widthPanel.value}%)` : '0',
)

const isDragging = ref(false)

const startDrag = (event: MouseEvent) => {
  event.preventDefault()
  isDragging.value = true
  document.body.style.userSelect = 'none'
}

const onDrag = (event: MouseEvent) => {
  if (!isDragging.value) return

  const navbarWidth = 71.6
  const newWidth = ((event.clientX - navbarWidth) / window.innerWidth) * 100
  if (newWidth >= 22 && newWidth <= 40) {
    widthPanel.value = newWidth
  }
}

const stopDrag = () => {
  isDragging.value = false
  document.body.style.userSelect = ''
}

const isGeneratePanel = ref(false)

onMounted(() => {
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
})

onUnmounted(() => {
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
})
</script>

<template>
  <div
    v-if="isMobile"
    class="fixed z-110 top-full left-0 w-screen h-screen bg-(--color-main-panel) transition-[top] duration-400"
    :class="isGeneratePanel ? 'top-0!' : ''"
  >
    <button
      @click="isGeneratePanel = !isGeneratePanel"
      class="btn-icon absolute z-100 -top-10 right-4 h-10! bg-(--color-main-panel) border border-b-0! rounded-b-none! border-(--color-brend-simple)"
    >
      <icon-brush width="20" />
    </button>

    <button
      @click="isGeneratePanel = !isGeneratePanel"
      class="btn-icon absolute z-100 top-4 right-4 bg-(--color-main-panel)"
    >
      <icon-chevron-right width="24" class="rotate-90" />
    </button>

    <div class="overflow-hidden">
      <slot name="panel" />
    </div>
  </div>

  <div
    v-else
    class="hidden lg:block fixed z-100 top-0 left-(--w-navbar) h-screen bg-(--color-main-panel) border-r border-r-(--color-input)"
    :style="{ width: `${widthPanel}%` }"
  >
    <div class="overflow-hidden h-full">
      <slot name="panel" />
    </div>

    <div
      class="absolute top-0 -right-1 w-1.5 h-full hover:bg-(--color-brend-simple)/70 cursor-col-resize transition"
      :class="{ 'bg-(--color-brend-simple)!': isDragging }"
      @mousedown="startDrag"
    ></div>
  </div>

  <div
    class="overflow-hidden relative flex min-h-screen flex-col pb-5 transition-[padding-top] duration-300"
    :class="paddingTop"
    :style="{ marginLeft: contentMarginLeft }"
  >
    <slot />
  </div>
</template>
