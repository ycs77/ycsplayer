<template>
  <div class="max-w-screen-2xl mx-auto">
    <header class="p-[--layout-gap] lg:p-[--layout-gap-lg]">
      <div class="px-4 py-2.5 flex justify-between bg-blue-950/50 rounded-lg lg:px-6 lg:py-3">
        <Link href="/rooms" class="font-bold tracking-wide">
          ycsPlayer
        </Link>

        <div class="flex items-center gap-4">
          <template v-if="!user">
            <Link href="/login" class="btn btn-primary btn-sm sm:btn-base">
              登入
            </Link>

            <Link href="/register" class="btn btn-primary btn-sm sm:btn-base">
              註冊
            </Link>
          </template>

          <Link
            v-else
            href="/logout"
            as="button"
            method="post"
            class="btn btn-primary btn-sm sm:btn-base"
          >
            登出
          </Link>
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
</script>
