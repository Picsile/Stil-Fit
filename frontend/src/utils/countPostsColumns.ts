import { computed, type ComputedRef } from 'vue'
import { useWindowSize } from '@vueuse/core'
import { useIsMobile } from '@/composables/useIsMobile'

export function countPostsColumns(defaultCount = 7, baseWidth = 1900): ComputedRef<number> {
  const { width } = useWindowSize({ includeScrollbar: false })
  const { isMobile } = useIsMobile()

  const itemWidth = baseWidth / defaultCount

  const columnsCount = computed(() => {
    let count = Math.floor(width.value / itemWidth)

    if (isMobile.value) {
      count++
    }

    return count > 0 ? count : 1
  })

  return columnsCount
}
