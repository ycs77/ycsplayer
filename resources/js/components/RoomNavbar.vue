<template>
  <div class="flex items-center gap-2 overflow-x-auto">
    <Button href="/rooms" only-icon>
      <template #icon><HeroiconsChevronLeft class="w-4 h-4" /></template>
    </Button>

    <Button :href="`/rooms/${roomId}`">
      <template #icon><HeroiconsPlayCircle class="w-4 h-4" /></template>
      播放器
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
