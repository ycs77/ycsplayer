<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <h1 class="text-2xl">點播房間</h1>

    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <div v-for="room in rooms.data">
        <Link :href="`/rooms/${room.id}`" class="block p-4 bg-blue-950/50 hover:bg-blue-900/50 rounded-lg transition-colors lg:p-6">
          <HeroiconsPlayCircle v-if="room.type === RoomType.Video" class="w-9 h-9 text-blue-500/50 mb-2" />
          <HeroiconsMusicalNote v-else-if="room.type === RoomType.Audio" class="w-9 h-9 text-blue-500/50 mb-2" />
          <h5 class="text-xl">{{ room.title }}</h5>
        </Link>
      </div>
    </div>

    <Pagination :collection="rooms" />
  </div>
</template>

<script setup lang="ts">
import { type Room, RoomType } from '@/types'

defineProps<{
  rooms: Paginator<Room>
}>()
</script>
