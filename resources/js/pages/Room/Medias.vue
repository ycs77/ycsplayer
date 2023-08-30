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
        <Card>
          <template #title>
            <div class="flex justify-between items-center">
              <h1 class="text-2xl">媒體檔案</h1>
              <button
                type="button"
                class="btn btn-primary"
                @click="showRoomUploadMediaModal = true"
              >
                <HeroiconsCloudArrowUp class="w-4 h-4 mr-1" />
                上傳檔案
              </button>
            </div>
          </template>

          <div class="mt-6">
            <ul v-if="medias.length" class="space-y-6">
              <li v-for="media in medias" :key="media.id">
                <div class="flex items-center">
                  <img
                    v-if="media.thumbnail"
                    :src="media.thumbnail"
                    class="w-28 shrink-0 rounded-lg aspect-video object-cover mr-2 sm:w-32"
                  />
                  <MediaPlaceholder v-else class="w-28 shrink-0 mr-2 sm:w-32" />

                  <div class="grow break-all">{{ media.name }}</div>

                  <div class="shrink-0 whitespace-nowrap">
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
          </div>
        </Card>
      </div>

    </div>

    <RoomUploadMediaModal
      v-model="showRoomUploadMediaModal"
      :room-id="room.id"
      :csrf-token="csrf_token"
      @uploaded="uploaded"
    />

  </div>
</template>

<script setup lang="ts">
import { useToast } from 'vue-toastification'
import type { Room, Media } from '@/types'

const props = defineProps<{
  room: Required<Room>
  csrf_token: string
  medias: Media[]
  can: {
    uploadMedias: boolean
    settings: boolean
  }
}>()

const showRoomUploadMediaModal = ref(false)

const toast = useToast()

function uploaded(message: string) {
  router.get(usePage().url, {}, {
    only: [...globalOnly, 'csrf_token', 'medias'],
    preserveScroll: true,
    onSuccess() {
      toast.success(message)
    },
  })
}

function deleteMedia(media: Media) {
  if (confirm(`確定要刪除 ${media.name} 嗎?`)) {
    router.delete(`/rooms/${props.room.id}/medias/${media.id}`, {
      only: [...globalOnly, 'csrf_token', 'medias'],
      preserveScroll: true,
    })
  }
}
</script>
