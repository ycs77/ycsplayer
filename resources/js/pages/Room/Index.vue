<template>
  <div>
    <div class="container py-8">
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <div v-for="room in rooms.data">
          <Link :href="`/rooms/${room.id}`" class="block p-4 border border-gray-200 hover:border-sky-500 rounded-md">
            <h5 class="text-gray-700 text-xl font-bold">{{ room.title }}</h5>
          </Link>
        </div>
      </div>

      <Pagination :collection="rooms" />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Room } from '@/types'

defineProps<{
  rooms: Paginator<Room>
}>()
</script>
