<script setup lang="ts">
import { useClipboard, useShare } from '@vueuse/core'
import IconLink from '../icons/IconLink.vue'

const { copy, copied } = useClipboard()
const { share: systemShare, isSupported: isShareSupported } = useShare()

const share = async () => {
  const url = window.location.href

  if (isShareSupported.value) {
    systemShare({
      title: document.title,
      url: url,
    }).catch(() => {
      // Если пользователь отменил или произошла ошибка, просто копируем в буфер
      copy(url)
    })
  } else {
    copy(url)
  }
}
</script>

<template>
  <button @click="share" class="btn-dark flex items-center h-10! transition-all">
    <IconLink width="18" />
    <span>{{ copied ? 'Скопировано!' : 'Поделиться' }}</span>
  </button>
</template>
