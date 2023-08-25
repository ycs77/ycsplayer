<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">

    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">

      <!-- 導覽列 -->
      <RoomNavbar class="col-span-12" :room-id="room.id" />

      <div class="col-span-12">
        <div class="max-w-screen-md mx-auto bg-blue-950/50 p-4 rounded-lg lg:p-6">
          <h1 class="text-2xl">房間設定</h1>

          <div class="mt-6">
            <form @submit.prevent="form.post(`/rooms/${room.id}/settings`)">
              <div class="space-y-6">

                <RoomTypeSelectField id="type" v-model="form.type" />

                <SwitchField
                  label="自動播放"
                  id="auto_play"
                  v-model="form.auto_play"
                />

                <SwitchField
                  label="播放完畢自動刪除"
                  id="auto_remove"
                  v-model="form.auto_remove"
                />

              </div>

              <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                  保存
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import { type Room } from '@/types'

const props = defineProps<{
  room: Required<Room>
}>()

const form = useForm({
  type: props.room.type,
  auto_play: props.room.auto_play,
  auto_remove: props.room.auto_remove,
})
</script>
