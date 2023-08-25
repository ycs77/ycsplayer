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

                <Field label="房間類型">
                  <RadioGroup v-model="selectedRoomType">
                    <div class="grid grid-cols-2 gap-2">
                      <RadioGroupOption
                        as="template"
                        v-for="roomType in roomTypes"
                        :key="roomType.value"
                        :value="roomType"
                        v-slot="{ checked }"
                      >
                        <div
                          :class="checked ? 'bg-blue-600/75 text-white ' : 'bg-blue-950/75 text-white'"
                          class="relative flex cursor-pointer rounded-lg px-5 py-4 shadow-md focus:outline-none"
                        >
                          <div class="flex w-full items-center justify-between">
                            <div>
                              <HeroiconsPlayCircle
                                v-if="roomType.value === RoomType.Video"
                                class="w-9 h-9 mb-2"
                                :class="checked ? 'text-white' : 'text-blue-500/50'"
                              />
                              <HeroiconsMusicalNote
                                v-else-if="roomType.value === RoomType.Audio"
                                class="w-9 h-9 mb-2"
                                :class="checked ? 'text-white' : 'text-blue-500/50'"
                              />

                              <RadioGroupLabel as="p" class="text-white font-medium">
                                {{ roomType.name }}
                              </RadioGroupLabel>
                            </div>
                            <div v-show="checked" class="shrink-0 text-white">
                              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="12" fill="#fff" fill-opacity="0.2" />
                                <path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                            </div>
                          </div>
                        </div>
                      </RadioGroupOption>
                    </div>
                  </RadioGroup>
                </Field>

                <SwitchField
                  label="自動播放"
                  v-model="form.auto_play"
                />

                <SwitchField
                  label="播放完畢自動刪除"
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
import { type Room, RoomType } from '@/types'

const props = defineProps<{
  room: Required<Room>
}>()

const roomTypes = [
  {
    name: '影片',
    value: RoomType.Video,
  },
  {
    name: '音樂',
    value: RoomType.Audio,
  },
]

const selectedRoomType = ref(
  roomTypes.find(roomType => roomType.value === props.room.type) ?? roomTypes[0]
)

const form = useForm({
  type: selectedRoomType.value.value,
  auto_play: props.room.auto_play,
  auto_remove: props.room.auto_remove,
})

watch(selectedRoomType, selectedRoomType => {
  form.type = selectedRoomType.value
})
</script>
