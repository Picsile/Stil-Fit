<script setup lang="ts">
import { postApi, viewApi } from '@/api/api'
import IconAddImage from '@/components/icons/IconAddImage.vue'
import IconAddSimple from '@/components/icons/IconAddSimple.vue'

import { Field, FieldArray, Form } from 'vee-validate'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import InputTextarea from '@/components/forms/InputTextarea.vue'
import Input from '@/components/forms/Input.vue'
import type { PostThingForm } from '@/types/post.types'
import IconClose from '@/components/icons/iconClose.vue'
import IconManyImages from '@/components/icons/IconManyImages.vue'
import ArrowBackSimple from '../ui/ArrowBackSimple.vue'
import IconEye from '../icons/IconEye.vue'
import IconEyeClose from '../icons/IconEyeClose.vue'

const router = useRouter()

// Теги
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

  // paste with spaces — split immediately
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

// Для теста
const availableTags = [
  'y2k',
  'оверсайз',
  'винтаж',
  'минимализм',
  'гранж',
  'спортивный',
  'кэжуал',
  'стритвир',
  'классика',
  'готика',
  'панк',
  'эстетика',
  'лето',
  'зима',
  'осень',
  'весна',
  'деним',
  'кожа',
  'хлопок',
  'лен',
]

const availableTitles = [
  'Штаны оверсайз',
  'Винтажная куртка',
  'Кроссовки белые',
  'Джинсы широкие',
  'Футболка базовая',
  'Худи черное',
  'Пальто шерстяное',
  'Ботинки кожаные',
  'Рубашка оверсайз',
  'Свитер вязаный',
]

const availableDescriptions = [
  'Стильная вещь в отличном состоянии',
  'Идеально подходит для повседневной носки',
  'Качественный материал, удобная посадка',
  'Универсальная модель на любой случай',
  'Трендовая вещь этого сезона',
  'Классика, которая никогда не выходит из моды',
  'Отличное сочетание стиля и комфорта',
  'Минималистичный дизайн для любого образа',
]

const getRandomItem = (arr: string[]) => arr[Math.floor(Math.random() * arr.length)]

const getRandomTags = () => {
  const shuffled = [...availableTags].sort(() => 0.5 - Math.random())
  return shuffled.slice(0, 3 + Math.floor(Math.random() * 3))
}

const postThingForm: PostThingForm = {
  visible_id: 1,
  category_id: 1,
  title: getRandomItem(availableTitles) ?? '',
  description: getRandomItem(availableDescriptions) ?? '',
  tags: '',
  links: ['https://'],
}

// Автозаполнение тегов
selectedTags.value = getRandomTags()

// Images
const images = ref<File[]>([])
const previews = ref<string[]>([])
const mainPreview = ref<number>(0)

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files) return

  images.value = [...images.value, ...Array.from(target.files)]
  images.value = images.value.slice(0, 10)

  previews.value = []

  images.value.forEach((file) => {
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

// Links
const links = ref<string[]>([''])

// Validation
const publicThingValidation = {
  title: (value: string) => {
    if (!value) return 'Введите название вещи'
    return true
  },

  description: (value: string) => {
    if (!value) return 'Введите описание вещи'
    return true
  },

  tags: (value: string) => {
    if (selectedTags.value.length < 3) {
      return 'Добавьте минимум 3 тега'
    }

    return true
  },

  'links[0]': (value: string) => {
    if (value === '') {
      return 'Добавьте хотя бы одну ссылку на покупку этой вещи'
    }

    return true
  },

  images: () => {
    if (images.value.length === 0) {
      return 'Добавьте хотя бы одно изображение'
    }

    return true
  },
}

const isLoad = ref(false)

const publicThing = async (values: any, { setErrors }: any) => {
  const formData = new FormData()

  // Add form values
  formData.append('visible_id', values.visible_id)
  formData.append('category_id', values.category_id)
  formData.append('title', values.title)
  formData.append('description', values.description)

  // Add tags
  for (const tag of selectedTags.value) {
    formData.append(`tags[]`, tag)
  }

  // Add links
  for (const link of values.links) {
    formData.append(`links[]`, link)
  }

  // Add images
  for (const image of images.value) {
    formData.append('images[]', image)
  }

  try {
    isLoad.value = true

    const data = await postApi.createPost(formData)

    if (data.status == 'success') {
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
    console.error('Ошибка при публикации вещи: ', e)
  } finally {
    isLoad.value = false
  }
}
</script>

<template>
  <Form
    @submit="publicThing"
    :validation-schema="publicThingValidation"
    :initial-values="postThingForm"
    class="flex flex-col w-full max-w-6xl h-fit max-h-182 gap-8 p-10"
  >
    <!-- Header form -->
    <div class="flex justify-between items-center">
      <div class="flex gap-8 items-center">
        <ArrowBackSimple path="/" />
        <div>
          <h3 class="text-40 text-2xl">Публикация вещи</h3>
        </div>
      </div>

      <div class="bg-(image:--color-brend) p-0.5 pb-1 rounded-2xl w-fit">
        <div class="relative flex bg-(--color-bg) p-0.5 rounded-[14px]">
          <!-- Visible -->
          <Field name="visible_id" type="radio" :value="1" v-slot="{ field }">
            <label
              class="has-checked:bg-(image:--color-brend) has-checked:text-white has-checked:opacity-100 flex items-center gap-1.5 text-black opacity-60 hover:opacity-75 text-sm p-1.5 px-4 rounded-xl cursor-pointer"
            >
              <input v-bind="field" type="radio" name="visible" class="hidden" checked />
              <IconEye width="22" />
              Публичный
            </label>
          </Field>

          <Field name="visible_id" type="radio" :value="2" v-slot="{ field }">
            <label
              class="has-checked:bg-(image:--color-brend) has-checked:text-white has-checked:opacity-100 flex items-center gap-1.5 text-black opacity-60 hover:opacity-75 text-sm p-1.5 px-4 rounded-xl cursor-pointer"
            >
              <input v-bind="field" type="radio" name="visible" class="hidden" />
              <IconEyeClose width="20" />
              Приватный
            </label>
          </Field>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="flex gap-8 pb-20">
      <!-- Files -->
      <div class="flex flex-1">
        <!-- File download -->
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

        <!-- File render -->
        <div v-if="previews.length" class="w-130">
          <!-- Image preview -->
          <div class="overflow-hidden relative w-130 aspect-square bg-(--color-input) rounded-xl">
            <img
              :key="previews[mainPreview]"
              :src="previews[mainPreview]"
              class="w-full h-full object-contain"
              alt=""
            />

            <div
              @click.stop="() => removeImage(mainPreview)"
              class="btn-opacity-dark absolute top-2 right-2 h-auto! aspect-square bg-transparent! hover:bg-(--color-dark)/50! p-1 rounded-lg!"
            >
              <IconClose class="w-7" stroke-width="1.8" />
            </div>
          </div>

          <!-- Other images -->
          <div
            class="overflow-x-scroll flex gap-2 w-full py-2"
            :class="previews.length < 5 ? 'scrollbar-none' : ''"
          >
            <!-- Image -->
            <div
              v-for="(preview, index) in previews"
              :key="index"
              @click="() => (mainPreview = index)"
              class="group preview overflow-hidden relative flex justify-center items-center min-w-24.5 w-24.5 aspect-square bg-(--color-hover-input) rounded-xl hover:brightness-75 cursor-pointer"
            >
              <img class="object-fit" :src="preview" alt="" />

              <div
                @click.stop="() => removeImage(index)"
                class="btn-opacity-dark absolute top-1 right-1 h-auto! aspect-square bg-transparent! hover:bg-(--color-dark)/50! p-1 rounded-lg! opacity-0 group-hover:opacity-100"
              >
                <IconClose class="w-5" stroke-width="1.8" />
              </div>
            </div>

            <!-- Add other image -->
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

              <input id="image" type="file" multiple @change="handleFileUpload" class="hidden" />
            </div>
          </div>
        </div>
      </div>

      <!-- Fields -->
      <div class="flex flex-1 flex-col gap-4 -mt-1">
        <!-- Title -->
        <Field name="title" v-slot="{ field, errorMessage }">
          <Input
            v-bind="field"
            v-model="field.value"
            type="text"
            :errorMessage="errorMessage"
            label="Название"
          />
        </Field>

        <!-- Description -->
        <Field name="description" v-slot="{ field, errorMessage }">
          <InputTextarea
            v-bind="field"
            v-model="field.value"
            :errorMessage="errorMessage"
            type="text"
            label="Описание"
            :height="30"
          />
        </Field>

        <!-- Tags -->
        <Field name="tags" v-slot="{ field, errorMessage }">
          <div class="relative">
            <div class="group flex flex-col">
              <label
                class="label text-(--color-text-second) mb-1 group-focus-within:text-indigo-500"
                >Теги</label
              >
              <input
                :value="tagInput"
                @input="handleTagInput"
                @keydown="handleTagKeydown"
                type="text"
                placeholder="Введите тег"
                class="w-full bg-(--color-input) hover:bg-(--color-hover-input) px-5 py-2.5 rounded-xl focus:outline focus:outline-indigo-500"
              />
            </div>

            <!-- Suggestions -->
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

        <!-- Added tags -->
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

        <!-- Links -->
        <FieldArray name="links" v-slot="{ fields, push, remove }">
          <div class="group flex flex-col">
            <label class="label text-(--color-text-second) mb-1 group-focus-within:text-indigo-500"
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
                v-if="index !== 0"
                type="button"
                @click="remove(index)"
                class="bg-(--color-input) hover:bg-(--color-hover-input) p-2.5 rounded-xl focus:outline focus:outline-indigo-500 cursor-pointer"
              >
                <IconClose />
              </button>
            </div>
          </div>

          <!-- Add links -->
          <button
            type="button"
            @click="push('')"
            class="flex justify-center gap-1 w-full bg-(--color-input) hover:bg-(--color-hover-input) py-2.5 rounded-xl -mt-4 focus:outline focus:outline-indigo-500 cursor-pointer"
          >
            <IconAddSimple width="21px" />
            <span>Добавить ссылку</span>
          </button>
        </FieldArray>

        <!-- Btn send -->
        <button
          type="submit"
          class="w-full text-center bg-(image:--color-brend) hover:bg-(image:--color-hover-brend) text-white py-2.5 rounded-xl mt-3 transition-all cursor-pointer"
        >
          Опубликовать
        </button>
      </div>
    </div>
  </Form>
</template>

<style scoped>
.input-add-image:hover .icon-add-image {
  scale: 1.05;
  transform: translateY(-2px);
}
</style>
