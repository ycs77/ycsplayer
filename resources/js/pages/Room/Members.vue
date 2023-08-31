<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">
      <!-- 導覽列 -->
      <RoomNavbar
        class="col-span-12"
        :room-id="room.id"
        :can-upload-medias="can.uploadMedias"
        :can-settings="can.settings"
      />

      <div class="col-span-12">
        <!-- 房間成員卡 -->
        <RoomMembers
          :members="members"
          :room-id="room.id"
          :can-invite="can.inviteMember"
          :can-remove="can.removeMember"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Echo } from '@/echo'
import type { Room, RoomMember } from '@/types'

const props = defineProps<{
  room: Required<Room>
  members: RoomMember[]
  can: {
    inviteMember: boolean
    removeMember: boolean
    uploadMedias: boolean
    settings: boolean
  }
}>()

onMounted(() => {
  Echo.join(`player.${props.room.id}`)
})

onMounted(() => {
  Echo.join(`player.${props.room.id}`)
})
</script>
