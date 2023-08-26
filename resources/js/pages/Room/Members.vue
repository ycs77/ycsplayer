<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">

    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">

      <!-- 導覽列 -->
      <RoomNavbar
        class="col-span-12"
        :room-id="room.id"
        :can-uplaod-files="can.uplaodFiles"
        :can-settings="can.settings"
      />

      <div class="col-span-12">
        <!-- 房間成員卡 -->
        <RoomMembers />
      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import type { Room } from '@/types'

defineProps<{
  room: Required<Room>
  can: {
    uplaodFiles: boolean
    settings: boolean
  }
}>()
</script>
