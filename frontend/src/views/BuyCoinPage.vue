<script setup lang="ts">
import AppDesktopLayout from '@/layouts/AppDesktopLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import Header from '@/components/Header.vue'
import IconProtection from '@/components/icons/IconProtection.vue'
import IconSuccess from '@/components/icons/IconSuccess.vue'
import Navbar from '@/components/Navbar.vue'
</script>

<template>
  <MainLayout>
    <Header />
    <div class="flex h-[calc(100vh-5rem)] min-h-0 w-full">
      <div class="flex flex-col items-center m-auto">
        <h1 class="font-[Caveat] text-4xl md:text-7xl mt-5 md:mt-0">Пополнение токенов</h1>

        <p
          class="font-[ComicRelief] text-lg md:text-[1.1rem] text-(--color-text-second) text-center mt-3"
        >
          Токены используются для генерации изображений
        </p>

        <div class="flex flex-wrap gap-6 md:gap-7 px-4 md:px-auto mt-6">
          <div
            class="flex flex-col items-center w-full md:w-70 border border-(--color-input) p-4 md:p-5 rounded-3xl shadow"
          >
            <img src="/static/buyCoinOne.png" alt="" class="w-40 opacity-75" />
            <span class="font-[ComicRelief] text-3xl mt-2">500</span>
            <span class="font-[ComicRelief] text-lg">Токенов</span>

            <p
              class="w-fit bg-(--color-brend-light) text-(--color-brend-simple) rounded-2xl p-1 px-4 mt-4"
            >
              Идеально для старта
            </p>

            <ul class="flex flex-col gap-1 w-full mt-6">
              <li class="flex gap-2">
                <IconSuccess width="24" stroke-width="2" class="text-(--color-brend-simple)" /> 500
                токенов
              </li>
              <li class="flex gap-2">
                <IconSuccess width="24" stroke-width="2" class="text-(--color-brend-simple)" />До
                100 изображений
              </li>
            </ul>

            <span class="font-semibold text-[1.7rem] mt-5 md:mt-8">299 ₽</span>

            <div class="flex items-center gap-2">
              <div class="relative flex justify-center items-center">
                <div
                  class="absolute top-1/2 left-0 w-full h-px bg-(--color-text-second) -rotate-11"
                ></div>
                <span class="text-lg text-(--color-text-second) px-2">498 ₽</span>
              </div>
              <span
                class="h-fit bg-(--color-brend-light) text-(--color-brend-simple) text-[1rem] rounded-full px-2"
                >50%</span
              >
            </div>

            
            <button
              class="btn-default w-full border border-(--color-brend-simple) text-(--color-brend-simple) rounded-xl mt-4"
            >
              Выбрать
            </button>
          </div>

          <div
            class="relative flex flex-col items-center w-full md:w-70 border border-(--color-brend-simple)/50 p-5 rounded-3xl shadow"
          >
            <div class="absolute -top-4 bg-(--color-brend-simple) text-white p-1 px-5 rounded-2xl">
              Выбирают чаще всего
            </div>

            <img src="/static/buyCoinTwo.png" alt="" class="w-40 opacity-75" />
            <span class="font-[ComicRelief] text-3xl mt-2">1000</span>
            <span class="font-[ComicRelief] text-lg">Токенов</span>

            <p
              class="w-fit bg-(--color-brend-light) text-(--color-brend-simple) rounded-2xl p-1 px-4 mt-4"
            >
              Оптимальный выбор
            </p>

            <ul class="flex flex-col gap-1 w-full mt-6">
              <li class="flex gap-2">
                <IconSuccess width="24" stroke-width="2" class="text-(--color-brend-simple)" /> 1000
                токенов
              </li>
              <li class="flex gap-2">
                <IconSuccess width="24" stroke-width="2" class="text-(--color-brend-simple)" />До
                200 изображений
              </li>
            </ul>

            <span class="font-semibold text-[1.7rem] mt-5 md:mt-8">549 ₽</span>

            <div class="flex items-center gap-2">
              <div class="relative flex justify-center items-center">
                <div
                  class="absolute top-1/2 left-0 w-full h-px bg-(--color-text-second) -rotate-11"
                ></div>
                <span class="text-lg text-(--color-text-second) px-2">1098 ₽</span>
              </div>
              <span
                class="h-fit bg-(--color-brend-light) text-(--color-brend-simple) text-[1rem] rounded-full px-2"
                >50%</span
              >
            </div>

            <button class="btn-accent w-full border border-(--color-brend-simple) rounded-xl mt-4">
              Выбрать
            </button>
          </div>

          <div
            class="flex flex-col items-center w-full md:w-70 border border-(--color-input) p-5 rounded-3xl shadow"
          >
            <img src="/static/buyCoinThere.png" alt="" class="w-40 opacity-75" />
            <span class="font-[ComicRelief] text-3xl mt-2">3000</span>
            <span class="font-[ComicRelief] text-lg">Токенов</span>

            <p
              class="w-fit bg-(--color-brend-light) text-(--color-brend-simple) rounded-2xl p-1 px-4 mt-4"
            >
              Максимальная выгода
            </p>

            <ul class="flex flex-col gap-1 w-full mt-6">
              <li class="flex gap-2">
                <IconSuccess width="24" stroke-width="2" class="text-(--color-brend-simple)" /> 3000
                токенов
              </li>
              <li class="flex gap-2">
                <IconSuccess width="24" stroke-width="2" class="text-(--color-brend-simple)" />До
                600 изображений
              </li>
            </ul>

            <span class="font-semibold text-[1.7rem] mt-5 md:mt-8">1299 ₽</span>

            <div class="flex items-center gap-2">
              <div class="relative flex justify-center items-center">
                <div
                  class="absolute top-1/2 left-0 w-full h-px bg-(--color-text-second) -rotate-11"
                ></div>
                <span class="text-lg text-(--color-text-second) px-2">2598 ₽</span>
              </div>
              <span
                class="h-fit bg-(--color-brend-light) text-(--color-brend-simple) text-[1rem] rounded-full px-2"
                >50%</span
              >
            </div>

            <button
              class="btn-default w-full border border-(--color-brend-simple) text-(--color-brend-simple) rounded-xl mt-4"
            >
              Выбрать
            </button>
          </div>
        </div>

        <div
          class="flex flex-wrap justify-center text-center md:text-start gap-2 p-4 border border-(--color-input) rounded-2xl m-4 md:m-0 md:mt-6 shadow"
        >
          <IconProtection width="36" stroke-width="1" class="opacity-50" />
          <div class="text-(--color-text-second)">
            <p>Безопасная оплата</p>
            <p>Все платежи защищены и обрабатываются надёжными сервисами.</p>
          </div>
          <div></div>
        </div>

        <p></p>
      </div>
    </div>
  </MainLayout>
</template>
