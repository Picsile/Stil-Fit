<script setup lang="ts">
import { ref, computed, onMounted, watch, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Field, FieldArray, Form } from 'vee-validate'
import { postApi, viewApi } from '@/api/api'
import { useGenerationDataStore } from '@/stores/generationData.stores'
import { useGenerationItems } from '@/composables/useGenerationItems'
import draggable from 'vuedraggable'
import MainLayout from '@/layouts/MainLayout.vue'
import Header from '@/components/Header.vue'
import ArrowBackSimple from '@/components/ui/ArrowBackSimple.vue'
import Input from '@/components/forms/Input.vue'
import InputTextarea from '@/components/forms/InputTextarea.vue'
import IconShirt from '@/components/icons/IconShirt.vue'
import IconManyImages from '@/components/icons/IconManyImages.vue'
import IconAddImage from '@/components/icons/IconAddImage.vue'
import IconAddSimple from '@/components/icons/IconAddSimple.vue'
import IconClose from '@/components/icons/iconClose.vue'
import IconEye from '@/components/icons/IconEye.vue'
import IconEyeClose from '@/components/icons/IconEyeClose.vue'
import { typeOptions } from '@/options/options'
import type { PostForm } from '@/types/post.types'
import type { PostPreviewData } from '@/types/view.types'
import IconGlasses from '@/components/icons/IconGlasses.vue'

const router = useRouter()
const route = useRoute()
const generationDataStore = useGenerationDataStore()
const { addedItemIds, loadItems } = useGenerationItems()

const selectedType = ref<number>(1)
const outfitItems = ref<PostPreviewData[]>([])

// Form
const postForm = reactive<PostForm>({
  type_id: 1,
  visible_id: 1,
  category_id: 1,
  title: '',
  description: '',
  tags: '',
  links: [],
})

// Следим за изменением типа и очищаем данные
watch(selectedType, (newType) => {
  if (newType === 1) {
    // Вещь - очищаем items
    outfitItems.value = []
  } else if (newType === 2) {
    // Образ - очищаем links
    postForm.links = []
  }
})

// Images
const images = ref<File[]>([])
const previews = ref<string[]>([])
const mainPreview = ref<number>(0)

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files) return

  const newFiles = Array.from(target.files)
  const totalImages = images.value.length + newFiles.length

  if (totalImages > 10) {
    images.value = [...images.value, ...newFiles].slice(0, 10)
  } else {
    images.value = [...images.value, ...newFiles]
  }

  // Добавляем новые превью к существующим
  newFiles.slice(0, 10 - previews.value.length).forEach((file) => {
    const reader = new FileReader()
    reader.onload = (event) => {
      previews.value.push(event.target?.result as string)
    }
    reader.readAsDataURL(file)
  })
}

const removeImage = (index: number) => {
  images.value.splice(index, 1)
  previews.value.splice(index, 1)

  if (mainPreview.value > previews.value.length - 1) {
    mainPreview.value = Math.max(previews.value.length - 1, 0)
  }
}

// Draggable images list
interface DraggableImage {
  preview: string
  file: File
}

const draggableImages = computed<DraggableImage[]>({
  get: () => {
    return previews.value.map((preview, index) => ({
      preview,
      file: images.value[index] as File,
    }))
  },
  set: (newValue: DraggableImage[]) => {
    previews.value = newValue.map((item) => item.preview)
    images.value = newValue.map((item) => item.file)

    if (mainPreview.value >= newValue.length) {
      mainPreview.value = Math.max(newValue.length - 1, 0)
    }
  },
})

// Tags
const tagInput = ref<string>('')
const selectedTags = ref<string[]>([])
const suggestedTags = ref<{ id: number; title: string }[]>([])
const showSuggestions = ref<boolean>(false)
const activeTagIndex = ref<number>(0)

const searchTags = async (query: string) => {
  const clean = query.replace(/^#+/, '').trim()
  if (!clean) {
    suggestedTags.value = []
    showSuggestions.value = false
    return
  }

  try {
    const data = await viewApi.getTags(clean)
    if (data.status === 'success') {
      suggestedTags.value = data.tags.filter(
        (tag: { title: string }) => !selectedTags.value.includes(tag.title),
      )
      showSuggestions.value = suggestedTags.value.length > 0
      activeTagIndex.value = 0
    }
  } catch (e) {
    console.error('Ошибка при получении тегов:', e)
  }
}

const pushTag = (raw: string) => {
  const clean = raw.replace(/^#+/, '').trim()
  if (clean && !selectedTags.value.includes(clean)) {
    selectedTags.value.push(clean)
  }
}

const addTag = (tag: string) => {
  pushTag(tag)
  tagInput.value = ''
  suggestedTags.value = []
  showSuggestions.value = false
}

const removeTag = (index: number) => {
  selectedTags.value.splice(index, 1)
}

const handleTagInput = (e: Event) => {
  const input = e.target as HTMLInputElement
  const value = input.value

  if (value.includes(' ')) {
    const parts = value.split(/\s+/)
    const remaining = parts[parts.length - 1]
    parts.slice(0, -1).filter(Boolean).forEach(pushTag)
    tagInput.value = remaining ?? ''
    input.value = remaining ?? ''
    suggestedTags.value = []
    showSuggestions.value = false
    if (remaining) searchTags(remaining)
    return
  }

  tagInput.value = value
  searchTags(value)
}

const handleTagKeydown = (e: KeyboardEvent) => {
  if (e.key === ' ') {
    e.preventDefault()
    const val = tagInput.value.trim()
    if (val) {
      pushTag(val)
      tagInput.value = ''
      suggestedTags.value = []
      showSuggestions.value = false
    }
    return
  }

  if (e.key === 'Enter') {
    e.preventDefault()
    if (showSuggestions.value && suggestedTags.value[activeTagIndex.value]) {
      addTag(suggestedTags.value[activeTagIndex.value]!.title)
    } else if (tagInput.value.trim()) {
      tagInput.value.trim().split(/\s+/).filter(Boolean).forEach(pushTag)
      tagInput.value = ''
      suggestedTags.value = []
      showSuggestions.value = false
    }
    return
  }

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    if (activeTagIndex.value < suggestedTags.value.length - 1) {
      activeTagIndex.value++
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    if (activeTagIndex.value > 0) {
      activeTagIndex.value--
    }
  } else if (e.key === 'Escape') {
    showSuggestions.value = false
  }
}

// Validation
const validationSchema = computed(() => {
  const base = {
    title: (value: string) => {
      if (!value) return 'Введите название'
      return true
    },
    tags: () => {
      if (selectedTags.value.length < 3) {
        return 'Добавьте минимум 3 тега'
      }
      return true
    },
    images: () => {
      if (images.value.length === 0 && previews.value.length === 0) {
        return 'Добавьте хотя бы одно изображение'
      }
      return true
    },
  }

  return base
})

const isLoad = ref(false)

const publicPost = async (values: any, { setErrors }: any) => {
  const formData = new FormData()

  formData.append('type_id', String(selectedType.value))
  formData.append('visible_id', String(postForm.visible_id))
  formData.append('category_id', String(postForm.category_id))
  formData.append('title', values.title)
  formData.append('description', values.description || '')

  for (const tag of selectedTags.value) {
    formData.append(`tags[]`, tag)
  }

  if (selectedType.value === 1 && values.links && Array.isArray(values.links)) {
    for (const link of values.links) {
      if (link && link.trim()) {
        formData.append(`links[]`, link)
      }
    }
  }

  if (selectedType.value === 2 && outfitItems.value.length > 0) {
    for (const item of outfitItems.value) {
      formData.append(`item_ids[]`, String(item.id))
    }
  }

  for (const image of images.value) {
    formData.append('images[]', image)
  }

  try {
    isLoad.value = true

    const data = await postApi.createPost(formData)

    if (data.status == 'success') {
      generationDataStore.clearSelection()
      await router.push({ name: 'home' })
    } else if (data.errorsValidation) {
      const errors = data.errorsValidation

      for (const key in errors) {
        if (errors.hasOwnProperty(key)) {
          errors[key] = errors[key][0]
        }
      }

      setErrors(data.errorsValidation)
    } else {
      throw new Error(data?.message)
    }
  } catch (e) {
    console.error('Ошибка при публикации: ', e)
  } finally {
    isLoad.value = false
  }
}

// Load data from generation
const loadGenerationData = async () => {
  if (route.query.from === 'generation') {
    selectedType.value = 2

    // Clear previous data
    previews.value = []
    images.value = []
    outfitItems.value = []

    // Load images, items and prompt from store
    if (generationDataStore.selectedImages.length > 0) {
      const imagePaths = generationDataStore.getImagePaths

      // Конвертируем URL в File объекты через бэкенд метод getFile
      for (const path of imagePaths) {
        try {
          const blob = await viewApi.getFile(path)
          const filename = path.split('/').pop() || 'image.jpg'
          const file = new File([blob], filename, { type: blob.type })

          images.value.push(file)

          // Создаем blob URL для превью
          const blobUrl = URL.createObjectURL(blob)
          previews.value.push(blobUrl)
        } catch (error) {
          console.error('Ошибка загрузки изображения:', error)
        }
      }

      outfitItems.value = generationDataStore.getAllItems
      postForm.description = JSON.stringify({ prompt: generationDataStore.generationPrompt })
    }
  }
}

onMounted(async () => {
  await loadGenerationData()
})

watch(
  () => route.fullPath,
  async () => {
    if (route.query.from === 'generation') {
      await loadGenerationData()
    }
  },
)
</script>

<template>
  <MainLayout>
    <Header :hide-boards="true" />

    <div class="flex min-h-[calc(100vh-5rem)] w-full">
      <Form
        @submit="publicPost"
        :validation-schema="validationSchema"
        :values="postForm"
        class="flex flex-col w-full max-w-312 h-fit gap-4 md:gap-8 px-4 pb-8 m-auto"
      >
        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-2">
          <div class="flex gap-8 items-center">
            <ArrowBackSimple path="/" />
            <h3 class="text-2xl">Новый пост</h3>
          </div>

          <div class="bg-(image:--color-brend) p-0.5 pb-1 rounded-2xl w-fit">
            <div class="relative flex bg-(--color-bg) p-0.5 rounded-[14px]">
              <Field name="visible_id" type="radio" :value="1" v-slot="{ field }">
                <label
                  class="has-checked:bg-(image:--color-brend) has-checked:text-white has-checked:opacity-100 flex items-center gap-1.5 text-black opacity-60 hover:opacity-75 text-sm p-1.5 px-4 rounded-xl cursor-pointer"
                >
                  <input
                    v-bind="field"
                    @change="postForm.visible_id = 1"
                    type="radio"
                    name="visible"
                    class="hidden"
                    checked
                  />
                  <IconEye width="22" />
                  Публичный
                </label>
              </Field>

              <Field name="visible_id" type="radio" :value="2" v-slot="{ field }">
                <label
                  class="has-checked:bg-(image:--color-brend) has-checked:text-white has-checked:opacity-100 flex items-center gap-1.5 text-black opacity-60 hover:opacity-75 text-sm p-1.5 px-4 rounded-xl cursor-pointer"
                >
                  <input
                    v-bind="field"
                    @change="postForm.visible_id = 2"
                    type="radio"
                    name="visible"
                    class="hidden"
                  />
                  <IconEyeClose width="20" />
                  Приватный
                </label>
              </Field>
            </div>
          </div>
        </div>

        <!-- Body -->
        <div class="flex flex-wrap md:flex-nowrap gap-8">
          <!-- Images -->
          <div class="flex w-full md:w-1/2">
            <div v-if="!previews.length" class="w-full">
              <label
                for="images"
                class="input-add-image flex flex-col gap-5 justify-center items-center w-full aspect-square bg-(--color-input) hover:bg-(--color-hover-input) rounded-xl cursor-pointer"
              >
                <IconManyImages class="icon-add-image -mb-3 transition" width="58" />
                <span>Выберите файл или перетащите его сюда</span>
                <span class="btn-default h-auto! p-1.5! px-4! border rounded-lg">Указать файл</span>
              </label>

              <Field name="images" v-slot="{ errorMessage }">
                <input id="images" type="file" multiple @change="handleFileUpload" class="hidden" />

                <div v-if="errorMessage" class="errorMessage text-sm text-red-500 mt-1">
                  {{ errorMessage }}
                </div>
              </Field>
            </div>

            <div v-if="previews.length" class="w-full">
              <div
                class="relative flex items-center justify-center aspect-square bg-(--color-input) p-2 rounded-xl"
              >
                <img
                  :key="previews[mainPreview]"
                  :src="previews[mainPreview]"
                  class="max-w-full max-h-full object-contain rounded-xl m-auto"
                  alt=""
                />

                <div
                  @click.stop="() => removeImage(mainPreview)"
                  class="btn-opacity-dark absolute top-2 right-2 h-auto! aspect-square p-1 rounded-lg! opacity-20 hover:opacity-100"
                >
                  <IconClose class="w-7" stroke-width="1.8" />
                </div>
              </div>

              <draggable
                v-model="draggableImages"
                item-key="preview"
                class="overflow-x-scroll flex gap-2 max-w-full py-2"
                :class="previews.length < 5 ? 'scrollbar-none' : ''"
              >
                <template #item="{ element, index }">
                  <div
                    @click="() => (mainPreview = index)"
                    class="group preview overflow-hidden relative flex justify-center items-center min-w-24.5 w-24.5 aspect-square bg-(--color-hover-input) rounded-xl hover:brightness-75 cursor-move"
                  >
                    <img class="object-fit" :src="element.preview" alt="" />

                    <div
                      @click.stop="() => removeImage(index)"
                      class="btn-opacity-dark absolute top-1 right-1 h-auto! aspect-square p-1 rounded-lg! opacity-20 hover:opacity-100"
                    >
                      <IconClose class="w-5" stroke-width="1.8" />
                    </div>
                  </div>
                </template>

                <template #footer>
                  <div v-if="previews.length < 10">
                    <label
                      for="image"
                      class="input-add-image flex flex-col gap-5 justify-center items-center w-24.5 aspect-square bg-(--color-input) hover:bg-(--color-hover-input) rounded-xl cursor-pointer"
                    >
                      <IconAddImage
                        class="icon-add-image -mb-1 stroke-(--color-hover-dark) transition duration-(--speed-transition)"
                        width="30"
                        stroke-width="1.4"
                      />
                    </label>

                    <input
                      id="image"
                      type="file"
                      multiple
                      @change="handleFileUpload"
                      class="hidden"
                    />
                  </div>
                </template>
              </draggable>
            </div>
          </div>

          <!-- Fields -->
          <div class="flex flex-col w-full md:w-1/2 gap-4">
            <div class="flex flex-col gap-4 -mt-1">
              <!-- Type selector -->
              <div class="flex flex-col gap-1">
                <label class="label text-(--color-text-second) mb-0.5">Тип публикации</label>

                <div class="flex bg-(--color-input) rounded-xl p-1">
                  <button
                    v-for="type in typeOptions"
                    :key="type.id"
                    @click="selectedType = type.id"
                    type="button"
                    class="flex justify-center items-center gap-2 w-full h-12 hover:bg-(--color-hover-input) rounded-[10px] cursor-pointer hover:opacity-100 transition-opacity duration-(--speed-transition)"
                    :class="
                      selectedType === type.id
                        ? 'bg-(--color-brend-simple)! text-white'
                        : 'opacity-60'
                    "
                  >
                    <IconShirt v-if="type.id === 1" width="20" stroke-width="1.8" />
                    <IconGlasses v-if="type.id === 2" width="20" />
                    <span v-if="type.id === 3" class="text-[16px]">?</span>
                    <span>{{ type.label }}</span>
                  </button>
                </div>
              </div>

              <Field name="title" v-slot="{ field, errorMessage }">
                <Input
                  v-bind="field"
                  v-model="field.value"
                  type="text"
                  :errorMessage="errorMessage"
                  label="Название"
                />
              </Field>

              <Field name="description" v-slot="{ field, errorMessage }">
                <InputTextarea
                  v-bind="field"
                  v-model="postForm.description"
                  :errorMessage="errorMessage"
                  type="text"
                  label="Описание"
                  :height="30"
                />
              </Field>

              <Field name="tags" v-slot="{ field, errorMessage }">
                <div class="relative">
                  <div class="group flex flex-col">
                    <label
                      class="label text-(--color-text-second) mb-1 group-focus-within:text-indigo-500"
                      >Теги</label
                    >
                    <div class="flex gap-1.5">
                      <input
                        :value="tagInput"
                        @input="handleTagInput"
                        @keydown="handleTagKeydown"
                        type="text"
                        placeholder="Введите тег"
                        class="flex-1 bg-(--color-input) hover:bg-(--color-hover-input) px-5 py-2.5 rounded-xl focus:outline focus:outline-indigo-500"
                      />
                      <button
                        type="button"
                        @click="addTag(tagInput)"
                        class="h-fit bg-(--color-input) hover:bg-(--color-hover-input) p-[0.7rem] rounded-xl focus:outline focus:outline-indigo-500 cursor-pointer"
                      >
                        Добавить
                      </button>
                    </div>
                  </div>

                  <div
                    v-if="showSuggestions"
                    class="absolute z-10 w-full mt-1 bg-(--color-main-panel) rounded-xl shadow-(--shadow-light) overflow-hidden"
                  >
                    <div
                      v-for="(tag, index) in suggestedTags"
                      :key="tag.id"
                      @click="addTag(tag.title)"
                      :class="[
                        'px-5 py-2.5 cursor-pointer hover:bg-(--color-input)',
                        { 'bg-(--color-input)': index === activeTagIndex },
                      ]"
                    >
                      {{ tag.title }}
                    </div>
                  </div>

                  <div v-if="errorMessage" class="errorMessage text-sm text-red-500 mt-1">
                    {{ errorMessage }}
                  </div>
                </div>
              </Field>

              <div v-if="selectedTags.length > 0" class="flex flex-wrap gap-2 -mt-2">
                <div
                  v-for="(tag, index) in selectedTags"
                  :key="index"
                  class="flex items-center gap-1 bg-(--color-brend-light) border border-(--color-brend-simple) text-(--color-brend-simple) py-0.5 px-3 rounded-xl cursor-pointer"
                >
                  <span>#{{ tag }}</span>
                  <div @click="removeTag(index)">
                    <IconClose width="18" />
                  </div>
                </div>
              </div>

              <!-- Links (только для вещей) -->
              <FieldArray v-if="selectedType === 1" name="links" v-slot="{ fields, push, remove }">
                <div class="group flex flex-col">
                  <label
                    class="label text-(--color-text-second) mb-1 group-focus-within:text-indigo-500"
                    >Ссылки</label
                  >

                  <div
                    v-for="(linkField, index) in fields"
                    :key="linkField.key"
                    class="flex gap-1.5 mb-2.5"
                  >
                    <Field :name="`links[${index}]`" v-slot="{ field, errorMessage }">
                      <div class="flex flex-col w-full">
                        <input
                          v-bind="field"
                          v-model="field.value"
                          type="text"
                          placeholder="https://"
                          class="w-full bg-(--color-input) hover:bg-(--color-hover-input) px-5 py-2.5 rounded-xl focus:outline focus:outline-indigo-500"
                        />
                        <div v-if="errorMessage" class="errorMessage text-sm text-red-500 mt-1">
                          {{ errorMessage }}
                        </div>
                      </div>
                    </Field>
                    <button
                      type="button"
                      @click="remove(index)"
                      class="h-fit bg-(--color-input) hover:bg-(--color-hover-input) p-[0.7rem] rounded-xl focus:outline focus:outline-indigo-500 cursor-pointer"
                    >
                      <IconClose width="20" />
                    </button>
                  </div>
                </div>

                <button
                  type="button"
                  @click="push('')"
                  class="flex justify-center gap-1 w-full bg-(--color-input) hover:bg-(--color-hover-input) py-2.5 rounded-xl -mt-4 focus:outline focus:outline-indigo-500 cursor-pointer"
                >
                  <IconAddSimple width="21px" />
                  <span>Добавить ссылку</span>
                </button>
              </FieldArray>

              <!-- Вещи в образе (только для образов) -->
              <div v-if="selectedType === 2 && outfitItems.length > 0" class="flex flex-col gap-2">
                <label class="label text-(--color-text-second)">Вещи в образе</label>

                <div
                  v-for="(item, index) in outfitItems"
                  :key="item.id"
                  class="flex items-center gap-3 bg-(--color-input) p-2 rounded-xl"
                >
                  <img
                    :src="item.main_image.path_preview"
                    :alt="item.title"
                    class="w-16! aspect-square img-cover rounded-lg"
                  />

                  <div class="flex-1 min-w-0">
                    <h4 class="font-medium truncate">{{ item.title }}</h4>
                    <p class="text-sm text-(--color-text-second) truncate">
                      {{ item.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <button
              type="submit"
              :disabled="isLoad"
              class="w-full h-12 text-center bg-(image:--color-brend) hover:bg-(image:--color-hover-brend) text-white py-2.5 rounded-xl mt-3 transition-all cursor-pointer"
            >
              {{ isLoad ? 'Публикуем...' : 'Опубликовать' }}
            </button>
          </div>
        </div>
      </Form>
    </div>
  </MainLayout>
</template>

<style scoped>
.input-add-image:hover .icon-add-image {
  scale: 1.05;
  transform: translateY(-2px);
}
</style>
