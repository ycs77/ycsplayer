<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">
      <!-- 導覽列 -->
      <RoomNavbar
        class="col-span-12"
        :room-id="room.id"
        :can-upload-medias="can.uploadMedias"
        can-settings
      />

      <div class="col-span-12">
        <Card title="房間設定">
          <form @submit.prevent="form.post(`/rooms/${room.id}/settings`)">
            <div class="space-y-6">
              <TextInput id="name" v-model="form.name" label="房間名稱" />
              <RoomTypeSelectField id="type" v-model="form.type" />
              <SwitchField id="auto_play" v-model="form.auto_play" label="自動播放" />
              <SwitchField id="auto_remove" v-model="form.auto_remove" label="播放完畢自動刪除" />
            </div>

            <div class="mt-6">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                保存
              </button>
            </div>
          </form>
        </Card>

        <Card v-if="can.delete" title="刪除房間" class="mt-8">
          <button type="button" class="btn btn-danger" @click="deleteRoom">
            刪除房間
          </button>
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
    delete: boolean
  }
}>()

const form = useForm({
  name: props.room.name,
  type: props.room.type,
  auto_play: props.room.auto_play,
  auto_remove: props.room.auto_remove,
})

function deleteRoom() {
  if (props.can.delete && confirm(`確定要刪除房間「${props.room.name}」?`)) {
    router.delete(`/rooms/${props.room.id}`)
  }
}
</script>
