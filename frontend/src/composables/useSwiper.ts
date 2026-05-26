import type { Swiper as SwiperType } from 'swiper'
import { ref } from 'vue'

export function useSwiper() {
  const swiperInstance = ref<SwiperType>()

  const onSwiper = (swiper: SwiperType) => {
    swiperInstance.value = swiper
  }

  const slideTo = (index: number) => {
    swiperInstance.value?.slideTo(index)
  }

  return {
    swiperInstance,
    onSwiper,
    slideTo,
  }
}
