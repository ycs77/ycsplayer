<template>
  <Field
    label="房間類型"
    :error="error || (id ? $page.props.errors[id] : undefined)"
    :tip="tip"
    :class="wrapperClass"
  >
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
            class="relative flex cursor-pointer rounded-lg px-5 py-4 shadow-md focus:outline-none"
            :class="{
              'bg-blue-600/75 text-white': checked,
              'bg-blue-950/75 text-white': !checked,
            }"
          >
            <div class="flex w-full justify-between">
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

                <RadioGroupLabel as="p" class="text-white font-medium select-none">
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
</template>

<script setup lang="ts">
import { RoomType } from '@/types'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  modelValue: RoomType
  id?: string
  error?: string
  tip?: string
  wrapperClass?: any
}>()

const emit = defineEmits<{
  'update:modelValue': [value: RoomType]
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

const selectedRoomType = computed<{
  name: string
  value: RoomType
}>({
  get() {
    return roomTypes.find(roomType => roomType.value === props.modelValue)!
  },
  set(roomType) {
    emit('update:modelValue', roomType.value)
  },
})
</script>
