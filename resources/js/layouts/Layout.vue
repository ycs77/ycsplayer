<template>
  <div class="max-w-screen-2xl mx-auto">
    <header class="p-[--layout-gap] lg:p-[--layout-gap-lg]">
      <div class="px-4 py-2.5 flex justify-between items-center flex-col bg-blue-950/50 rounded-lg md:flex-row lg:px-6 lg:py-3">
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

        <div
          class="w-full flex-col justify-center items-center gap-4 mt-4 mb-2 md:flex md:flex-row md:mt-0 md:mb-0 md:w-auto"
          :class="{
            'flex': showMenu,
            'hidden': !showMenu,
          }"
        >
          <template v-if="!user">
            <Link href="/login" class="btn btn-primary btn-sm w-full sm:btn-base md:w-auto">
              登入
            </Link>

            <Link href="/register" class="btn btn-primary btn-sm w-full sm:btn-base md:w-auto">
              註冊
            </Link>
          </template>

          <template v-else>
            <Menu as="div" class="w-full relative" v-slot="{ close }">
              <Float
                placement="bottom-end"
                :offset="8"
                floating-as="template"
              >
                <MenuButton class="w-full flex items-center md:-my-1 md:max-w-[160px]">
                  <img
                    class="w-8 h-8 rounded-full mr-2"
                    :src="user.avatar ?? '/images/user.svg'"
                  />
                  <div class="tracking-wide whitespace-nowrap truncate min-w-0">
                    {{ user.name }}
                  </div>
                </MenuButton>

                <MenuItems
                  class="w-full p-1.5 space-y-1.5 bg-blue-900/50 rounded-md shadow-md shadow-blue-950/50 backdrop-blur-md overflow-hidden focus:outline-none md:w-40"
                  @click="close"
                >
                  <MenuItem v-slot="{ active }">
                    <Link
                      href="/rooms"
                      class="block w-full px-4 py-1.5 text-left text-sm rounded-md transition-colors"
                      :class="{ 'bg-blue-900 text-white': active }"
                    >
                      點播房間
                    </Link>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <Link
                      href="/user/settings"
                      class="block w-full px-4 py-1.5 text-left text-sm rounded-md transition-colors"
                      :class="{ 'bg-blue-900 text-white': active }"
                    >
                      帳號設定
                    </Link>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <Link
                      href="/logout"
                      as="button"
                      method="post"
                      class="block w-full px-4 py-1.5 text-left text-sm rounded-md transition-colors"
                      :class="{ 'bg-blue-900 text-white': active }"
                    >
                      登出
                    </Link>
                  </MenuItem>
                </MenuItems>
              </Float>
            </Menu>
          </template>
        </div>
      </div>
    </header>

    <main>
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { user } = useAuth()

const showMenu = ref(false)
</script>
