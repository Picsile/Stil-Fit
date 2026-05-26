<script setup lang="ts">
import type { GenerationData } from '@/types/generation.types'
import GenerationImage from './GenerationImage.vue'
import IconGlasses from '@/components/icons/IconGlasses.vue'
import IconEye from '@/components/icons/IconEye.vue'
import IconEyeClose from '@/components/icons/IconEyeClose.vue'
import IconShirt from '@/components/icons/IconShirt.vue'
import IconDoubleArrow from '@/components/icons/IconDoubleArrow.vue'
import { computed, ref } from 'vue'
import GenerationImageSkeleton from './GenerationImageSkeleton.vue'
import IconError from '@/components/icons/IconError.vue'
import IconRatio from '@/components/icons/IconRatio.vue'
import { visibleOptions } from '@/options/options'

interface Props {
  generationData: GenerationData
}

const props = defineProps<Props>()

const isOpenDetail = ref<boolean>(false)

const formattedDate = computed(() => {
  const raw = props.generationData.created_at as Date | string | undefined
  if (!raw) return ''
  const d = new Date(typeof raw === 'string' ? raw.replace(' ', 'T') : raw)
  return isNaN(d.getTime()) ? '' : d.toLocaleString('ru-RU')
})

const displayError = computed(() => {
  const msg = props.generationData.error
  if (!msg || /APIMart|Не удалось загрузить изображение/i.test(msg)) {
    return 'Проблема с соединением, попробуйте позже'
  }
  return msg
})

const table = computed(() => [
  {
    label: 'Промпт',
    value: props.generationData.prompt,
  },
  {
    label: 'Видимость',
    value: props.generationData.visible_id === visibleOptions[0].id ? 'Публичный' : 'Приватный',
  },
  {
    label: 'Соотношение',
    value: props.generationData.ratio,
  },
  { label: 'Количество', value: props.generationData.quantity },
  {
    label: 'Дата',
    value: formattedDate.value,
  },
])
</script>

<template>
  <div
    class="flex flex-col bg-(--color-main-panel) p-3 md:p-5 pb-0! border border-(--color-input) rounded-2xl mt-3 md:mt-5 mx-2 md:mx-4 shadow-(--shadow-base)"
  >
    <!-- Tags и дата -->
    <div class="flex justify-between items-center mb-3">
      <div class="flex gap-1.5">
        <!-- Видимость -->
        <div
          class="flex items-center gap-2 px-3 rounded-lg"
          :class="
            generationData.visible_id === visibleOptions[0].id
              ? 'bg-lime-50 border border-lime-500 text-lime-500'
              : 'bg-zinc-50 border border-zinc-500 text-zinc-500'
          "
        >
          <IconEye
            v-if="generationData.visible_id === visibleOptions[0].id"
            width="16px"
          />
          <IconEyeClose v-else width="16px" />
          <span>{{
            generationData.visible_id === visibleOptions[0].id
              ? 'Публичный'
              : 'Приватный'
          }}</span>
        </div>

        <!-- Ratio -->
        <div
          class="flex items-center gap-2 bg-pink-50 border border-pink-400 text-pink-400 px-3 rounded-lg"
        >
          <IconRatio width="16px" />
          <span> {{ generationData.ratio }} </span>
        </div>
      </div>

      <!-- Дата -->
      <span class="text-(--color-text-second)">{{ formattedDate }}</span>
    </div>

    <!-- Промпт -->
    <span class="mb-3 truncate">{{ generationData.prompt }}</span>

    <!-- Прикрепленные вещи и внешность -->
    <div v-if="generationData.items.length" class="flex gap-2 mb-4">
      <div
        v-for="post in generationData.items"
        :key="post.main_image.id"
        class="h-10 md:w-14 aspect-square hover:brightness-75 cursor-pointer transition"
      >
        <img
          :src="post.main_image.path_preview"
          :alt="String(post.main_image.id)"
          class="img-cover rounded-lg"
        />
      </div>
    </div>

    <!-- Сгенерированные изображения -->
    <div class="columns-2 md:columns-4 gap-1 md:gap-2 mb-4">
      <GenerationImage
        v-if="generationData.status === 'Completed' || generationData.status === 'Generating'"
        v-for="image in generationData.generated_images"
        :key="image.id"
        :image-data="image"
        :all-images="generationData.generated_images"
        :generation-items="generationData.items"
        :generation-prompt="generationData.prompt"
      />

      <GenerationImageSkeleton
        v-if="generationData.status === 'Generating'"
        v-for="n in Math.max(
          0,
          generationData.quantity - (generationData.generated_images?.length ?? 0),
        )"
        :key="`skel-${n}`"
        :ratio="generationData.ratio"
      />
    </div>

    <div
      v-if="generationData.status === 'Error'"
      class="flex gap-1.5 items-center font-medium bg-indigo-50 text-violet-500 p-4 border border-violet-400 rounded-xl mb-4"
    >
      <IconError width="18" class="shrink-0 mb-0.5" />
      <p><span class="mr-2">Ошибка:</span>{{ displayError }}</p>
    </div>

    <!-- Подробнее -->
    <div class="relative text-[13px] min-h-8">
      <div
        @click="isOpenDetail = !isOpenDetail"
        class="select-none absolute left-0 bottom-0 flex justify-center items-center w-full h-12 bg-(--color-main-panel) text-(--color-text-second) hover:text-black transition cursor-pointer"
      >
        <div class="flex items-center gap-2">
          <IconDoubleArrow
            width="16px"
            height="16px"
            class="transition-transform duration-300"
            :class="{ 'rotate-180': isOpenDetail }"
          />
          <span>Подробнее</span>
        </div>
      </div>

      <!-- Table -->
      <div
        class="grid transition-all duration-500 ease-in-out overflow-hidden"
        :style="{ gridTemplateRows: isOpenDetail ? '1fr' : '0fr' }"
      >
        <div class="min-h-0">
          <div class="pb-12">
            <div
              class="bg-(--color-input)/50 border border-(--color-border) rounded-lg"
            >
              <div
                v-for="row in table"
                :key="row.label"
                class="grid grid-cols-[30%_1fr] gap-4 p-4 py-3 border-b border-(--color-border) last:border-b-0"
              >
                <span class="text-(--color-text-second)">{{ row.label }}</span>
                <span class="wrap-break-word">{{ row.value }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
