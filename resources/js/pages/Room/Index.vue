<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl">房間列表</h1>
      <div>
        <button
          v-if="can.create"
          type="button"
          class="btn btn-primary"
          @click="showRoomModal = true"
        >
          <HeroiconsPlus class="w-4 h-4 mr-1" />
          創建房間
        </button>
      </div>
    </div>

    <div class="mt-4">
      <div v-if="rooms.data.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <div v-for="room in rooms.data" :key="room.id">
          <Link :href="`/rooms/${room.id}`" class="block p-4 bg-blue-950/50 hover:bg-blue-900/50 rounded-lg transition-colors lg:p-6">
            <RoomLogo :room="room" class="w-9 h-9 mb-2" />
            <h5 class="text-xl">{{ room.name }}</h5>
          </Link>
        </div>
      </div>

      <div v-else class="py-32 flex justify-center items-center bg-blue-950/50 text-center text-lg rounded-lg">
        現在還沒有加入房間喔！<br>趕快加入跟朋友一起看影片、聽音樂吧~
      </div>

      <Pagination :collection="rooms" />
    </div>

    <RoomFormModal v-if="can.create" v-model="showRoomModal" />
  </div>
</template>

<script setup lang="ts">
import { type Room } from '@/types'

defineProps<{
  rooms: Paginator<Room>
  can: {
    create: boolean
  }
}>()

const showRoomModal = ref(false)
</script>
