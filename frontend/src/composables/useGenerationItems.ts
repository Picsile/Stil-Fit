import { ref, onMounted } from 'vue'
import { useAuthRedirect } from './useAuthRedirect'

const STORAGE_KEY = 'added-item-ids'

const addedItemIds = ref<number[]>([])

export function useGenerationItems() {
  const { requireAuth } = useAuthRedirect()

  const loadItems = () => {
    const stored = localStorage.getItem(STORAGE_KEY)
    addedItemIds.value = stored ? JSON.parse(stored) : []
  }

  const isItemAdded = (postId: number) => {
    return addedItemIds.value.includes(postId)
  }

  const toggleItem = (postId: number) => {
    if (!requireAuth()) return false

    const index = addedItemIds.value.indexOf(postId)

    if (index === -1) {
      addedItemIds.value.push(postId)
    } else {
      addedItemIds.value.splice(index, 1)
    }

    localStorage.setItem(STORAGE_KEY, JSON.stringify(addedItemIds.value))
    return true
  }

  onMounted(() => {
    loadItems()
  })

  return {
    addedItemIds,
    isItemAdded,
    toggleItem,
    loadItems,
  }
}
