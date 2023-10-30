<template>
  <CardAuth title="刪除帳號">
    <div>
      刪除帳號 E-mail 已發送至 <span class="text-blue-400 break-words">{{ email }}</span>，並且當前帳號已登出，請開啟您的信箱點擊刪除帳號連結。
    </div>
  </CardAuth>
</template>

<script setup lang="ts">
defineProps<{
  email: string
}>()

defineOptions({ inheritAttrs: false })
</script>
