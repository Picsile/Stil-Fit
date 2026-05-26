import { defineStore } from 'pinia'

export const useImageStore = defineStore('image', {
  state: () => {
    return {
      isModalOpen: false,
      currentImageUrl: '' as string,
      images: [] as string[],
      currentIndex: 0,
    }
  },

  actions: {
    openImage(imageUrl: string, allImages?: string[]) {
      this.currentImageUrl = imageUrl
      this.isModalOpen = true
      document.body.style.overflow = 'hidden'

      if (allImages && allImages.length > 0) {
        this.images = allImages
        this.currentIndex = allImages.indexOf(imageUrl)
      } else {
        this.images = [imageUrl]
        this.currentIndex = 0
      }
    },

    closeImage() {
      this.isModalOpen = false
      document.body.style.overflow = ''
      this.currentImageUrl = ''
      this.images = []
      this.currentIndex = 0
    },

    nextImage() {
      if (this.images.length > 0 && this.currentIndex < this.images.length - 1) {
        this.currentIndex++
        this.currentImageUrl = this.images[this.currentIndex] ?? ''
      }
    },

    prevImage() {
      if (this.images.length > 0 && this.currentIndex > 0) {
        this.currentIndex--
        this.currentImageUrl = this.images[this.currentIndex] ?? ''
      }
    },
  },
})
