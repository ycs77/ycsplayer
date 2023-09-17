<template>
  <div class="max-w-screen-2xl mx-auto">
    <header class="p-[--layout-gap] lg:p-[--layout-gap-lg]">
      <div class="px-4 py-2.5 flex justify-between items-center flex-col bg-blue-950/50 rounded-lg md:flex-row lg:px-6">
        <div class="w-full flex justify-between items-center md:w-auto">
          <Link v-if="user" href="/rooms" class="font-bold tracking-wide">
            ycsPlayer
          </Link>
          <a v-else href="/" class="font-bold tracking-wide">
            ycsPlayer
          </a>

          <button
            type="button"
            class="relative z-10 p-0.5 ml-auto text-white md:hidden focus:outline-none"
            @click="showMenu = !showMenu"
          >
            <div class="relative w-8 h-8">
              <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -rotate-90"
                enter-to-class="opacity-100 rotate-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 rotate-0"
                leave-to-class="opacity-0 rotate-90"
              >
                <HeroiconsBars3 v-if="!showMenu" class="absolute w-8 h-8 text-white" />
                <HeroiconsXMark v-else class="absolute w-8 h-8 text-white" />
              </Transition>
            </div>
          </button>
        </div>

        <!-- 電腦版 -->
        <div class="hidden md:flex md:justify-center md:items-center md:space-x-4">
          <template v-if="!user">
            <Link href="/login" class="btn btn-primary py-1.5">
              登入
            </Link>

            <Link href="/register" class="btn btn-primary py-1.5">
              註冊
            </Link>
          </template>

          <template v-else>
            <Menu v-slot="{ close }" as="div" class="relative">
              <MenuButton class="w-full flex items-center -my-0.5 max-w-[160px]">
                <Avatar class="w-9 h-9 mr-2" :src="user.avatar" />
                <div class="tracking-wide whitespace-nowrap truncate min-w-0">
                  {{ user.name }}
                </div>
              </MenuButton>

              <Transition
                enter-active-class="transition-[transform,opacity] duration-200 origin-top-right ease-out"
                enter-from-class="scale-95 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transition-[transform,opacity] duration-150 origin-top-right ease-in"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-95 opacity-0"
              >
                <MenuItems
                  as="ul"
                  class="absolute top-full right-0 w-40 p-1.5 mt-1.5 space-y-1.5 bg-blue-900/50 rounded-md shadow-md shadow-blue-950/50 backdrop-blur-md overflow-hidden focus:outline-none"
                >
                  <MenuItem
                    v-for="item in userMenu"
                    v-slot="{ active }"
                    :key="item.href"
                    as="li"
                    @click.capture="close"
                  >
                    <component
                      :is="item.is ?? 'Link'"
                      :href="item.is !== 'button' ? item.href : undefined"
                      class="block w-full px-4 py-1.5 text-left text-sm rounded-md transition-colors"
                      :class="{ 'bg-blue-900 text-white': active }"
                      @click="item.onClick"
                    >
                      {{ item.label }}
                    </component>
                  </MenuItem>
                </MenuItems>
              </Transition>
            </Menu>
          </template>
        </div>

        <!-- 手機版 -->
        <div v-if="showMenu" class="w-full mt-4 mb-2 space-y-4 md:hidden">
          <template v-if="!user">
            <Link href="/login" class="block w-full py-1 text-left">
              登入
            </Link>

            <Link href="/register" class="block w-full py-1 text-left">
              註冊
            </Link>
          </template>

          <template v-else>
            <Link href="/user/settings" class="w-full flex items-center">
              <Avatar class="w-8 h-8 mr-2" :src="user.avatar" />
              <div class="tracking-wide whitespace-nowrap truncate min-w-0">
                {{ user.name }}
              </div>
            </Link>

            <component
              :is="item.is ?? 'Link'"
              v-for="item in userMenu"
              :key="item.href"
              :href="item.is !== 'button' ? item.href : undefined"
              class="block w-full py-1 text-left"
              @click="item.onClick"
            >
              {{ item.label }}
            </component>
          </template>
        </div>
      </div>
    </header>

    <main>
      <slot />
    </main>

    <ModalsContainer />
  </div>
</template>

<script setup lang="ts">
import { ModalsContainer } from 'vue-final-modal'

const { user } = useAuth()

const showMenu = ref(false)

const userMenu = [
  {
    href: '/rooms',
    label: '房間列表',
  },
  {
    href: '/user/settings',
    label: '帳號設定',
  },
  {
    href: '/logout',
    label: '登出',
    is: 'button',
    onClick() {
      router.post('/logout')
    },
  },
] as {
  href: string
  label: string
  is?: string
  onClick?: () => void
}[]

router.on('finish', () => {
  showMenu.value = false
})
</script>
