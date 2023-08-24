<template>
  <div>
    <div class="container py-8">
      <div class="mb-4">
        <Link href="/rooms" class="text-lg text-sky-500">返回列表</Link>
      </div>

      <h1 class="mb-4 text-2xl font-semibold">{{ room.title }}</h1>

      <div class="grid gap-8 md:grid-cols-2">
        <div>
          <Player
            v-if="current_playing"
            ref="player"
            :key="current_playing.id"
            :room-id="room.id"
            :src="current_playing.url"
            :type="current_playing.type"
            :poster="current_playing.thumbnail ?? undefined"
            @ended="ended"
          />

          <div v-else>
            <div class="flex justify-center items-center bg-gray-200 text-lg rounded-lg aspect-video">
              請選擇播放項目
            </div>
          </div>
        </div>

        <div>
          <ul>
            <li v-for="item in playlist_items">
              <div
                v-if="item.id === current_playing?.id"
                class="block px-4 py-1.5 w-full bg-red-100 border-l-4 border-red-400 text-left select-none"
              >
                {{ item.title }}
              </div>
              <button
                v-else
                type="button"
                class="px-4 py-1.5 w-full bg-gray-50 hover:bg-gray-100 border-l-4 border-transparent text-left select-none"
                @click="next(item)"
              >
                {{ item.title }}
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Echo } from '@/echo'
import Player from '@/components/Player.vue'
import type { Room, PlaylistItem } from '@/types'

const props = defineProps<{
  room: Required<Room>
  current_playing: PlaylistItem | null
  playlist_items: PlaylistItem[]
}>()

const current_playing = ref(props.current_playing) as Ref<PlaylistItem | null>
const playlist_items = ref(props.playlist_items) as Ref<PlaylistItem[]>

const player = ref(null) as Ref<InstanceType<typeof Player> | null>

function ended() {
  router.post(`/rooms/${props.room.id}/next`, {
    current_playing_id: props.current_playing?.id,
  })
}

function next(item: PlaylistItem) {
  router.post(`/rooms/${props.room.id}/play/${item.id}`)
}

// 監聽當有其他人切換影片時的事件
function onPlayerlistItemClicked() {
  router.reload()
}

watch(() => props.current_playing, () => {
  current_playing.value = props.current_playing
})

watch(() => props.playlist_items, () => {
  playlist_items.value = props.playlist_items
})

watch(player, (v, ov, invalidate) => {
  if (!player.value) return

  Echo.join(`player.${props.room.id}`)
    .listen('PlayerPlayed', player.value?.onPlayerPlayed)
    .listen('PlayerPaused', player.value?.onPlayerPaused)
    .listen('PlayerSeeked', player.value?.onPlayerSeeked)
    .listen('PlayerlistItemClicked', onPlayerlistItemClicked)

  invalidate(() => {
    Echo.leave(`player.${props.room.id}`)
  })
}, { immediate: true })
</script>
