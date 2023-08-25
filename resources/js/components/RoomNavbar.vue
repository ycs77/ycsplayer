<template>
  <div class="flex items-center gap-2 overflow-x-auto overflow-y-hidden">
    <Button href="/rooms">
      <template #icon><HeroiconsHome class="w-4 h-4" /></template>
      房間列表
    </Button>

    <Button
      :href="`/rooms/${roomId}`"
      :active="$page.url === `/rooms/${roomId}`"
    >
      <template #icon><HeroiconsPlayCircle class="w-4 h-4" /></template>
      播放器
    </Button>

    <Button
      :href="`/rooms/${roomId}/members`"
      :active="$page.url === `/rooms/${roomId}/members`"
      class="xl:hidden"
    >
      <template #icon><HeroiconsUserGroup class="w-4 h-4" /></template>
      成員
    </Button>

    <Button :href="`/rooms/${roomId}`">
      <template #icon><HeroiconsDocumentText class="w-4 h-4" /></template>
      檔案
    </Button>

    <Button :href="`/rooms/${roomId}`">
      <template #icon><HeroiconsCog6Tooth class="w-4 h-4" /></template>
      設定
    </Button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  roomId: number
}>()
</script>
