<script setup lang="ts">
import { generationApi } from '@/api/api'
import InputTextarea from '@/components/forms/InputTextarea.vue'
import GenerationAddedItem from '@/modules/generation/components/GenerationAddedItem.vue'
import IconAddFace from '@/components/icons/IconAddFace.vue'
import IconAddSimple from '@/components/icons/IconAddSimple.vue'
import IconChevronLeft from '@/components/icons/IconChevronLeft.vue'
import IconChevronRight from '@/components/icons/IconChevronRight.vue'
import IconEye from '@/components/icons/IconEye.vue'
import IconFace from '@/components/icons/IconFace.vue'
import IconRatio from '@/components/icons/IconRatio.vue'
import IconShirt from '@/components/icons/IconShirt.vue'
import IconStars from '@/components/icons/IconStars.vue'
import IconThings from '@/components/icons/IconThings.vue'
import ModalAddItemToGenerate from '@/components/modals/ModalAddItemToGenerate.vue'
import { typeOptions, visibleOptions, ratioOptions, resolutionOptions } from '@/options/options'
import type { GenerationData, GenerationForm } from '@/types/generation.types'
import { Field, Form } from 'vee-validate'
import { computed, ref, reactive, onMounted, watch } from 'vue'
import { viewApi } from '@/api/api'
import type { PostPreviewData } from '@/types/view.types'
import type { BoardId } from '@/types/board.types'
import { useUserStore } from '@/stores/user.stores'
import { useBoardStore } from '@/stores/board.stores'
import { useGenerationItems } from '@/composables/useGenerationItems'
import { useAuthRedirect } from '@/composables/useAuthRedirect'
import IconClose from '../icons/iconClose.vue'
import IconFitCoin from '../icons/IconFitCoin.vue'

// Состояния
const userStore = useUserStore()
const boardStore = useBoardStore()
const { addedItemIds, toggleItem, loadItems } = useGenerationItems()
const { requireAuth, requireEmailVerified } = useAuthRedirect()

const emit = defineEmits<{
  (e: 'update-generation', generation: GenerationData): void
  (e: 'remove-generation', id: number | null): void
}>()

// Generated
const generationConfig = localStorage.getItem('generation-config')
const generationForm = reactive<GenerationForm>(
  generationConfig
    ? JSON.parse(generationConfig)
    : {
        type_id: 2,
        visible_id: 1,
        prompt: 'Сгенерируй классный образ с этими вещами и моей внешностью',
        ratio_id: 4,
        resolution_id: 1,
        quantity: 4,
      },
)

// Appearance
const generationAppearance = ref<File[]>([])

const handleAppearanceUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files) {
    generationAppearance.value = [...generationAppearance.value, ...Array.from(target.files)]
  }
  target.value = ''
}

const removeAppearance = (index: number) => {
  generationAppearance.value.splice(index, 1)
}

const getImageUrl = (file: File) => {
  return URL.createObjectURL(file)
}

// Boards
const activeBoardId = ref<BoardId>('top')

// Items
const isAddItems = ref<boolean>(false)
const generationItems = ref<PostPreviewData[]>([])
const generationCost = computed(() => {
  const costPerImage = generationForm.resolution_id === 2 ? 8 : 5
  return generationForm.quantity * costPerImage
})

const addItem = (post: PostPreviewData) => {
  toggleItem(post.id)

  const index = generationItems.value.findIndex((item) => item.id === post.id)
  if (index === -1) {
    generationItems.value.push(post)
  } else {
    generationItems.value.splice(index, 1)
  }
}

// Validation
const generationValidation = {
  prompt: (value: string) => {
    if (!value) return 'Введите промпт'
    return true
  },
  items: () => {
    if (generationItems.value.length + generationAppearance.value.length > 10) {
      return 'Максимальное количество фото 10'
    }
    return true
  },
}

// Generate
const isGenerateDisabled = ref<boolean>(false)

const generate = async (values: any, { setErrors }: any) => {
  if (isGenerateDisabled.value) return
  if (!requireAuth()) return
  if (!requireEmailVerified()) return

  isGenerateDisabled.value = true
  setTimeout(() => (isGenerateDisabled.value = false), 1500)

  try {
    const formData = new FormData()

    // Подготовка данных
    formData.append('type_id', values.type_id)
    formData.append('visible_id', values.visible_id)
    formData.append('prompt', values.prompt)
    formData.append('ratio_id', values.ratio_id)
    formData.append('resolution_id', values.resolution_id)
    formData.append('quantity', values.quantity)

    for (const image of generationAppearance.value) {
      formData.append('appearances[]', image)
    }

    for (const addedItemId of addedItemIds.value) {
      formData.append('itemIds[]', String(addedItemId))
    }

    // Создаём скелет генерации с id: null
    const skeletonGeneration: GenerationData = {
      id: null,
      type: typeOptions[values.type_id - 1]?.title as 'Thing' | 'Outfit',
      visible_id: values.visible_id,
      prompt: values.prompt,
      ratio: ratioOptions[values.ratio_id - 1]?.value ?? '',
      resolution: resolutionOptions[values.resolution_id - 1]?.value ?? '',
      quantity: values.quantity,
      created_at: new Date(),
      appearances: [...generationAppearance.value],
      items: [...generationItems.value],
      generated_images: [],
      status: 'Generating',
    }

    // Добавляем скелет в список генераций
    emit('update-generation', skeletonGeneration)

    // Запрос на бэкенд
    const data = await generationApi.createGeneration(formData)
    console.log(data)

    if (data.status === 'success') {
      // Обновляем скелет с реальным id
      const updatedGeneration: GenerationData = {
        ...skeletonGeneration,
        id: data.generation.id,
        status: data.generation.status,
      }

      emit('update-generation', updatedGeneration)

      // Update user's fitcoins
      if (data.quantity_fitcoins !== undefined && userStore.user) {
        userStore.user.quantity_fitcoins = data.quantity_fitcoins
      }
    } else if (data.errorsValidation) {
      setErrors(data.errorsValidation)
      // Удаляем скелет при ошибке валидации
      emit('remove-generation', null)
    } else {
      console.error('Ошибка генерации:', data.message)
      // Удаляем скелет при ошибке
      emit('remove-generation', null)
    }
  } catch (error) {
    console.error('Ошибка при отправке запроса:', error)
    // Удаляем скелет при ошибке
    emit('remove-generation', null)
  }
}

// Start
onMounted(async () => {
  loadItems()

  if (addedItemIds.value.length > 0) {
    const data = await viewApi.getPostsByIds(addedItemIds.value)

    if (data.status === 'success') {
      generationItems.value = data.posts
    }
  }

  if (userStore.user) {
    boardStore.getBoards(userStore.user.id)
  }
})

watch(
  generationForm,
  (newVal) => {
    localStorage.setItem('generation-config', JSON.stringify(newVal))
  },
  { deep: true },
)
</script>

<template>
  <Form
    @submit="generate"
    :validation-schema="generationValidation"
    :initial-values="generationForm"
    v-slot="{ setErrors }"
    class="flex flex-col h-screen"
  >
    <div class="overflow-y-scroll flex flex-col gap-8 h-full px-4 md:px-8 pb-8">
      <!-- Label -->
      <div class="flex items-center min-h-20 -mb-5">
        <h3 class="text-xl font-medium text-indigo-500">Создание изображения</h3>
      </div>

      <!-- Visible -->
      <Field name="visible_id" v-model="generationForm.visible_id" v-slot="{ field, errorMessage }">
        <div class="flex flex-col gap-1">
          <label class="flex items-center gap-2 mb-0.5 group-focus-within:text-indigo-500">
            <IconEye width="18" height="18" />
            <span
              >Выберите
              <span class="text-blue-500 font-medium">видимость</span>
              генерации</span
            >
          </label>

          <div class="flex bg-(--color-input) rounded-xl p-1">
            <div
              v-for="resolution in visibleOptions"
              :key="resolution.id"
              class="flex items-center w-full"
            >
              <button
                type="button"
                @click="
                  (field.onChange(resolution.id), (generationForm.visible_id = resolution.id))
                "
                class="flex flex-col justify-center items-center gap-1.5 w-full h-10 hover:bg-(--color-hover-input) rounded-[10px] cursor-pointer hover:opacity-100 transition-opacity duration-(--speed-transition)"
                :class="field.value === resolution.id ? 'bg-(--color-main-panel)!' : 'opacity-60'"
              >
                <span>{{ resolution.label }}</span>
              </button>
            </div>
          </div>
        </div>
      </Field>

      <!-- Prompt -->
      <Field name="prompt" v-model="generationForm.prompt" v-slot="{ field, errorMessage }">
        <InputTextarea
          v-bind="field"
          v-model="field.value"
          @update:modelValue="generationForm.prompt = $event"
          :errorMessage="errorMessage"
          type="text"
          label="Промпт *"
          :height="30"
          class="*:text-(--color-text)"
        />
      </Field>

      <!-- ImagesModel -->
      <div class="flex flex-col w-fit">
        <label class="flex items-center gap-2 mb-2.5 group-focus-within:text-indigo-500">
          <IconFace width="18" height="18" />
          Добавите
          <span class="text-blue-500 font-medium">свои фото</span>
        </label>

        <div class="flex flex-wrap gap-2">
          <div
            v-for="(appearance, index) in generationAppearance"
            :key="index"
            class="group relative overflow-hidden aspect-square w-28 rounded-xl"
          >
            <img :src="getImageUrl(appearance)" class="img-cover transition" />

            <div
              @click="removeAppearance(index)"
              class="btn-opacity-dark absolute top-1.5 right-1.5 h-auto! aspect-square bg-transparent! hover:bg-(--color-dark)/50! p-1 rounded-lg! opacity-0 group-hover:opacity-100"
            >
              <IconClose class="w-5" stroke-width="1.8" />
            </div>
          </div>

          <label
            class="group flex flex-col gap-1 justify-center items-center aspect-square w-28 bg-(--color-input) hover:bg-(--color-hover-input) border border-dashed rounded-xl opacity-60 cursor-pointer"
          >
            <div class="relative">
              <IconAddFace class="-scale-x-100" width="41" height="41" />
              <IconAddSimple
                width="20"
                class="absolute bg-(--color-input) group-hover:bg-(--color-hover-input) -bottom-1.5 -right-1 rounded-xl p-0.5"
              />
            </div>
            <span>Добавить</span>
            <input
              type="file"
              accept="image/*"
              multiple
              @change="handleAppearanceUpload"
              class="hidden"
            />
          </label>
        </div>
      </div>

      <!-- Items -->
      <Field name="items" v-model="generationItems" v-slot="{ errorMessage }">
        <div class="flex flex-col">
          <label class="flex items-center gap-2 mb-2.5 group-focus-within:text-indigo-500">
            <IconShirt width="18" height="18" />
            Добавьте <span class="text-blue-500 font-medium">вещи</span> для образа
          </label>

          <div class="flex flex-wrap gap-2">
            <GenerationAddedItem
              v-for="addedItem in generationItems"
              :key="addedItem.id"
              :post="addedItem"
              @add-item="addItem"
            />

            <div
              v-if="generationItems.length < 5"
              @click="isAddItems = true"
              class="group flex flex-col gap-2 justify-center items-center aspect-square w-28 bg-(--color-input) hover:bg-(--color-hover-input) border border-dashed rounded-xl opacity-60 cursor-pointer"
            >
              <div class="relative">
                <IconThings width="38" height="38" />
                <IconAddSimple
                  width="20"
                  class="absolute bg-(--color-input) group-hover:bg-(--color-hover-input) -bottom-2 -right-1 rounded-xl p-0.5"
                />
              </div>
              <span class="select-none">Добавить</span>
            </div>
          </div>

          <div v-if="errorMessage" class="text-red-500 mt-1 -mb-3">
            {{ errorMessage }}
          </div>
        </div>
      </Field>

      <!-- Ratio -->
      <Field name="ratio_id" v-model="generationForm.ratio_id" v-slot="{ field, errorMessage }">
        <div class="flex flex-col gap-1">
          <label class="flex items-center gap-2 mb-0.5 group-focus-within:text-indigo-500">
            <IconRatio width="18" height="18" />
            <span>Выберите <span class="text-blue-500 font-medium">соотношение</span></span>
          </label>

          <div class="flex bg-(--color-input) rounded-xl p-1">
            <div v-for="ratio in ratioOptions" :key="ratio.id" class="flex items-center w-full">
              <button
                type="button"
                @click="(field.onChange(ratio.id), (generationForm.ratio_id = ratio.id))"
                class="flex flex-col justify-center items-center gap-1.5 w-full h-18 hover:bg-(--color-hover-input) pt-1.5 rounded-[10px] cursor-pointer hover:opacity-100 transition-opacity duration-(--speed-transition)"
                :class="field.value === ratio.id ? 'bg-(--color-main-panel)!' : 'opacity-60'"
              >
                <div
                  class="bg-(--color-border) border-2 border-(--color-text-second) rounded-xs"
                  :class="ratio.frameSize"
                ></div>
                <span>{{ ratio.value }}</span>
              </button>
              <div
                v-if="
                  ratioOptions.length !== ratio.id &&
                  field.value - 1 !== ratio.id &&
                  field.value !== ratio.id
                "
                class="h-14 border-r border-(--color-border)"
              ></div>
            </div>
          </div>
        </div>
      </Field>

      <!-- Resolution -->
      <Field
        name="resolution_id"
        v-model="generationForm.resolution_id"
        v-slot="{ field, errorMessage }"
      >
        <div class="flex flex-col gap-1">
          <label class="flex items-center gap-2 mb-0.5 group-focus-within:text-indigo-500">
            <IconRatio width="18" height="18" />
            <span>Выберите <span class="text-blue-500 font-medium">разрешение</span></span>
          </label>

          <div class="flex bg-(--color-input) rounded-xl p-1">
            <div
              v-for="resolution in resolutionOptions"
              :key="resolution.id"
              class="flex items-center w-full"
            >
              <button
                type="button"
                @click="
                  (field.onChange(resolution.id), (generationForm.resolution_id = resolution.id))
                "
                class="flex flex-col justify-center items-center gap-1.5 w-full h-8 hover:bg-(--color-hover-input) rounded-[10px] cursor-pointer hover:opacity-100 transition-opacity duration-(--speed-transition)"
                :class="field.value === resolution.id ? 'bg-(--color-main-panel)!' : 'opacity-60'"
              >
                <span>{{ resolution.value }}</span>
              </button>
              <div
                v-if="
                  resolutionOptions.length !== resolution.id &&
                  field.value - 1 !== resolution.id &&
                  field.value !== resolution.id
                "
                class="h-4 border-r border-(--color-border)"
              ></div>
            </div>
          </div>

          <div
            v-if="generationForm.resolution_id === 2"
            class="text-sm text-(--color-text-second) mt-1 -mb-2.5"
          >
            Не работает с форматами 4:3 и 3:4
          </div>
        </div>
      </Field>
    </div>

    <!-- Quantity & Run -->
    <div
      class="flex items-center gap-2 h-24 bg-(--color-main-panel) border-t-2 border-(--color-border) rounded-t-2xl px-4 md:px-8"
    >
      <!-- Quantity -->
      <Field name="quantity" v-model="generationForm.quantity" v-slot="{ field, handleChange }">
        <div
          class="flex flex-col justify-content-center items-center h-12 bg-(--color-input) px-2 pt-1 rounded-xl"
        >
          <span class="text-[11px] text-(--color-text-second) -mt-[0.1rem]">Количество</span>
          <div class="flex justify-content-center items-center gap-1">
            <IconChevronLeft
              @click="generationForm.quantity = Math.max(1, generationForm.quantity - 1)"
              class="scale-95 hover:scale-100 opacity-50 hover:opacity-100 transition cursor-pointer"
            />
            <span class="w-3 text-center text-[16px]">{{ field.value }}</span>
            <IconChevronRight
              @click="generationForm.quantity = Math.min(4, generationForm.quantity + 1)"
              class="scale-95 hover:scale-100 opacity-50 hover:opacity-100 transition cursor-pointer"
            />
          </div>
        </div>
      </Field>

      <!-- Run -->
      <button
        type="submit"
        :disabled="isGenerateDisabled"
        class="flex items-center gap-1.5 btn-accent w-full disabled:opacity-60 disabled:cursor-not-allowed"
      >
        <IconStars width="22" class="shrink-0" />
        Сгенерировать
      </button>

      <div
        class="flex flex-col justify-content-center items-center min-w-16 h-12 bg-(--color-input) px-2 pt-1 rounded-xl"
      >
        <span class="text-[11px] text-(--color-text-second) -mt-[0.1rem]">Затраты</span>
        <div class="flex items-center gap-1 text-[16px]">
          <img src="/static/fitcoin.png" alt="" class="w-4 mb-0.5" />
          <span class="font-[ComicRelief]">{{ generationCost }}</span>
        </div>
      </div>
    </div>
  </Form>

  <!-- Modal -->
  <ModalAddItemToGenerate
    v-if="isAddItems"
    :added-item-ids="addedItemIds"
    :boardTitles="boardStore.boardTitles"
    v-model:is-add-items="isAddItems"
    @add-item="addItem"
  />
</template>
