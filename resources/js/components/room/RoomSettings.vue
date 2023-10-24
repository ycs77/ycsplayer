<template>
  <div class="bg-blue-950/50 p-4 rounded-lg">
    <h4 class="text-xl mb-4">房間設定</h4>
    <form @submit.prevent="updateRoom">
      <div class="space-y-6">
        <TextInput id="name" v-model="form.name" label="房間名稱" />
        <RoomTypeSelectField id="type" v-model="form.type" />
        <SwitchField id="auto_play" v-model="form.auto_play" label="連續播放" />
        <SwitchField id="auto_remove" v-model="form.auto_remove" label="播放完畢自動刪除" />
      </div>

      <div class="mt-6">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          保存
        </button>
      </div>
    </form>
  </div>

  <div v-if="canDeleteRoom" class="bg-blue-950/50 p-4 rounded-lg">
    <h4 class="text-xl mb-4">刪除房間</h4>
    <button type="button" class="btn btn-danger" @click="deleteRoom">
      刪除房間
    </button>
  </div>
</template>

<script setup lang="ts">
import type { Room } from '@/types'

const props = defineProps<{
  room: Required<Room>
  canDeleteRoom: boolean
}>()

const form = useForm({
  name: props.room.name,
  type: props.room.type,
  auto_play: props.room.auto_play,
  auto_remove: props.room.auto_remove,
})

function updateRoom() {
  form.put(`/rooms/${props.room.id}`, {
    only: [...globalOnly, 'room'],
    preserveScroll: true,
  })
}

function deleteRoom() {
  if (props.canDeleteRoom && confirm(`確定要刪除房間「${props.room.name}」?`)) {
    router.delete(`/rooms/${props.room.id}`)
  }
}
</script>
