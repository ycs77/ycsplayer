<template>
  <div>
    <div class="mb-[--layout-gap]">
      <FileUpload
        :key="fileInputKey"
        :target="`/rooms/${room.id}/upload`"
        :file-type="['mp4', 'mp3']"
        :csrf-token="csrfToken"
        :error="fileError"
        button-class="relative btn btn-list-item rounded-lg"
        @start="uploadStart"
        @success="uploaded"
        @error="uploadFail"
      >
        <template #progressing="{ progressPer }">
          <div class="relative btn btn-list-item hover:bg-blue-950/50 rounded-lg overflow-hidden">
            <div class="text-center">上傳中... 先不要離開...</div>
            <div class="absolute inset-x-0 bottom-0 h-1.5 bg-blue-900/50 overflow-hidden">
              <div
                class="bg-blue-500/50 h-full transition-[width] duration-500"
                :style="{ width: `${progressPer}%` }"
              />
            </div>
          </div>
        </template>
      </FileUpload>
    </div>

    <ul v-if="medias.length" class="bg-blue-950/50 rounded-lg overflow-hidden">
      <li v-for="media in medias" :key="media.id">
        <div class="flex items-center p-2 w-full text-left select-none">
          <MediaLoading v-if="media.converting" class="w-28 max-w-[40%] shrink-0 mr-2 sm:w-32" />
          <img
            v-else-if="media.thumbnail"
            :src="media.thumbnail"
            class="w-28 max-w-[40%] shrink-0 rounded-lg aspect-video object-cover mr-2 sm:w-32"
          >
          <MediaPlaceholder v-else class="w-28 max-w-[40%] shrink-0 mr-2 sm:w-32" />

          <div class="grow break-all">{{ media.name }}</div>

          <div v-if="!media.converting" class="ml-1 shrink-0 whitespace-nowrap">
            <button
              type="button"
              class="btn btn-sm btn-danger"
              @click="$emit('deleteMedia', media)"
            >
              刪除
            </button>
          </div>
        </div>
      </li>
    </ul>

    <div v-else class="px-2 py-24 flex justify-center items-centers text-center bg-blue-950/50 text-lg rounded-lg">
      在這裡可以上傳影片/音樂一起來觀賞~
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Media, Room } from '@/types'

defineProps<{
  room: Required<Room>
  medias: Media[]
  csrfToken: string
}>()

const emit = defineEmits<{
  uploaded: [message: string | null]
  deleteMedia: [media: Media]
}>()

const fileInputKey = ref(Date.now())
const fileError = ref<string | undefined>(undefined)

function uploadStart() {
  fileError.value = undefined
}

function uploaded(message: string | null) {
  updateKey()
  emit('uploaded', message)
}

function uploadFail(message: string) {
  updateKey()
  fileError.value = message
}

function updateKey() {
  fileInputKey.value = Date.now()
}
</script>
