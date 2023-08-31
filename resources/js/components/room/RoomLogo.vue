<template>
  <HeroiconsPlayCircle v-if="room.type === RoomType.Video" :class="colorClass" />
  <HeroiconsMusicalNote v-else-if="room.type === RoomType.Audio" :class="colorClass" />
</template>

<script setup lang="ts">
import { type Room, RoomType } from '@/types'

withDefaults(defineProps<{
  room: Room
  colorClass?: string
}>(), {
  colorClass: 'text-blue-500/50',
})
</script>
