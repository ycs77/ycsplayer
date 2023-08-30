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
        <Card title="房間設定">
          <form @submit.prevent="form.post(`/rooms/${room.id}/settings`)">
            <div class="space-y-6">
              <RoomTypeSelectField id="type" v-model="form.type" />
              <SwitchField label="自動播放" id="auto_play" v-model="form.auto_play" />
              <SwitchField label="播放完畢自動刪除" id="auto_remove" v-model="form.auto_remove" />
            </div>

            <div class="mt-6">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                保存
              </button>
            </div>
          </form>
        </Card>
      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import type { Room } from '@/types'

const props = defineProps<{
  room: Required<Room>
  can: {
    uploadMedias: boolean
    settings: boolean
  }
}>()

const form = useForm({
  type: props.room.type,
  auto_play: props.room.auto_play,
  auto_remove: props.room.auto_remove,
})
</script>
