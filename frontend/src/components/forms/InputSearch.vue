<script setup lang="ts">
import { ref } from 'vue'
import { computed } from 'vue'
import IconSearchCorner from '@/components/icons/IconSearchCorner.vue'
import IconSearch from '@/components/icons/IconSearch.vue'

interface Props {
  isSearchItem?: boolean
  showBorder?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isSearchItem: true,
  showBorder: false,
})

const modelValue = defineModel<string>()

const isFocused = ref(false)

const isActive = computed(() => isFocused.value || modelValue.value)
</script>

<template>
  <div class="relative z-75 w-full h-12! md:h-(--h-header) cursor-text">
    <!-- Icon search -->
    <div
      class="pointer-events-none overflow-hidden absolute left-4 flex items-center h-full z-50"
    >
      <div
        class="flex items-center gap-2 text-black/60 transition"
        :class="
          isActive ? '-translate-y-12 opacity-0' : 'translate-y-0 opacity-100'
        "
      >
        <IconSearch width="20" />
        <span class="text-[14px]">Поиск</span>
      </div>
    </div>

    <!-- Input -->
    <input
      type="text"
      name="search"
      v-model="modelValue"
      @focus="isFocused = true"
      @blur="isFocused = false"
      class="relative z-25 flex w-full h-full bg-(--color-input) hover:bg-(--color-hover-input) focus:bg-(--color-main-panel) px-4 rounded-2xl focus:outline-offset-2 focus:outline-indigo-400"
    />

    <!-- Search item -->
    <!-- <button
      v-if="isSearchItem"
      class="btn-in-input absolute z-25 top-1/2 -translate-y-1/2 right-1"
    >
      <IconSearchCorner />
    </button> -->

    <div
      v-if="showBorder"
      class="absolute z-10 flex items-center inset-0 -m-[3px] rounded-[18px] overflow-hidden"
    >
      <div
        class="w-full aspect-square spinning-gradient"
        style="
          background: conic-gradient(
            from 0deg,
            #6a53fe 0%,
            #8e7bfe 25%,
            #c3b8ff 50%,
            #4932e6 75%,
            #6a53fe 100%
          );
        "
      ></div>
    </div>
  </div>
</template>

<style scoped>
.spinning-gradient {
  animation: spina 4s linear infinite;
  /* Центрируем точку вращения */
  transform-origin: center;
}

@keyframes spina {
  /* 0% — Верх. Быстрый старт */
  0% {
    transform: rotate(0deg);
  }

  /* За 15% времени долетаем до 70° (очень быстро) */
  15% {
    transform: rotate(70deg);
  }

  /* Зависаем на правом боку: за 20% времени проходим всего 40° */
  35% {
    transform: rotate(110deg);
  }

  /* Опять ускоряемся: за 15% времени долетаем до низа (180°) */
  50% {
    transform: rotate(180deg);
  }

  /* За 15% времени быстро пролетаем низ до 250° */
  65% {
    transform: rotate(250deg);
  }

  /* Зависаем на левом боку: за 20% времени проходим всего 40° */
  85% {
    transform: rotate(290deg);
  }

  /* Быстро возвращаемся в верхнюю точку */
  100% {
    transform: rotate(360deg);
  }
}
</style>
