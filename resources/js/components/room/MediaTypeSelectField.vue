<template>
  <Field
    label="媒體類型"
    :error="error || (id ? $page.props.errors[id] : undefined)"
    :tip="tip"
    :class="wrapperClass"
  >
    <RadioGroup v-model="selectedPlayerType">
      <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        <RadioGroupOption
          as="template"
          v-for="playerType in playerTypes"
          :key="playerType.value"
          :value="playerType"
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
                  v-if="playerType.value === PlayerType.Video"
                  class="w-9 h-9 mb-2"
                  :class="checked ? 'text-white' : 'text-blue-500/50'"
                />
                <HeroiconsMusicalNote
                  v-else-if="playerType.value === PlayerType.Audio"
                  class="w-9 h-9 mb-2"
                  :class="checked ? 'text-white' : 'text-blue-500/50'"
                />
                <TablerBrandYoutubeFilled
                  v-else-if="playerType.value === PlayerType.YouTube"
                  class="w-9 h-9 mb-2"
                  :class="checked ? 'text-white' : 'text-blue-500/50'"
                />

                <RadioGroupLabel as="p" class="text-white font-medium select-none">
                  {{ playerType.name }}
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
import { PlayerType } from '@/types'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  modelValue: PlayerType
  id?: string
  error?: string
  tip?: string
  wrapperClass?: any
}>()

const emit = defineEmits<{
  'update:modelValue': [value: PlayerType]
}>()

const playerTypes = [
  {
    name: '影片',
    value: PlayerType.Video,
  },
  {
    name: '音樂',
    value: PlayerType.Audio,
  },
  {
    name: 'YouTube',
    value: PlayerType.YouTube,
  },
]

const selectedPlayerType = computed<{
  name: string
  value: PlayerType
}>({
  get() {
    return playerTypes.find(playerType => playerType.value === props.modelValue)!
  },
  set(playerType) {
    emit('update:modelValue', playerType.value)
  },
})
</script>
