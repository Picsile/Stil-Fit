<script setup lang="ts">
import LoaderCat from '@/components/ui/LoaderCat.vue'
import { computed } from 'vue'

interface Props {
  ratio: string
}

const props = defineProps<Props>()

const ratioStyle = computed(() => props.ratio.replace(':', '/'))
</script>

<template>
  <div
    class="overflow-hidden relative rounded-xl bg-(--color-input) animate-pulse transition duration-(--speed-transition)"
    :style="{ aspectRatio: ratioStyle }"
  >
    <LoaderCat width="50%" />
  </div>
</template>
