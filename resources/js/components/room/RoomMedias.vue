<template>
  <div>
    <div>
      <button
        type="button"
        class="btn btn-list-item rounded-lg mb-[--layout-gap]"
        @click="$emit('clickUpload')"
      >
        <HeroiconsCloudArrowUp class="w-4 h-4 mr-1" />
        上傳檔案
      </button>
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
              @click="deleteMedia(media)"
            >
              刪除
            </button>
          </div>
        </div>
      </li>
    </ul>

    <div v-else class="py-24 flex justify-center items-centers text-center bg-blue-950/50 text-lg rounded-lg">
      在這裡可以上傳影片/音樂一起來觀賞~
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Media, Room } from '@/types'

const props = defineProps<{
  room: Required<Room>
  medias: Media[]
}>()

defineEmits<{
  clickUpload: []
}>()

function deleteMedia(media: Media) {
  if (confirm(`確定要刪除 ${media.name} 嗎?`)) {
    router.delete(`/rooms/${props.room.id}/medias/${media.id}`, {
      only: [...globalOnly, 'csrfToken', 'currentPlaying', 'playlistItems', 'medias'],
      preserveScroll: true,
    })
  }
}
</script>
