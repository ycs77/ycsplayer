<template>
  <div class="px-[--layout-gap] pb-[calc(var(--layout-gap)+3rem)] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">

    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">

      <!-- 導覽列 -->
      <RoomNavbar class="col-span-12" :room-id="room.id" />

      <div class="col-span-12 md:col-span-8 lg:col-span-9">
        <div>
          <!-- 播放器 -->
          <div v-if="currentPlaying" class="rounded-lg overflow-hidden">
            <Player
              ref="player"
              :key="currentPlaying.id"
              :room-id="room.id"
              :src="currentPlaying.url"
              :type="currentPlaying.type"
              :poster="currentPlaying.thumbnail ?? undefined"
              @ended="ended"
            />
          </div>

          <!-- 無播放項目時的提示 -->
          <div v-else>
            <div class="flex justify-center items-center bg-blue-950/50 text-blue-300 text-lg rounded-lg aspect-video">
              請選擇播放項目
            </div>
          </div>
        </div>
      </div>

      <div class="col-span-12 md:col-span-4 lg:col-span-3">
        <div class="grid gap-[--layout-gap] lg:gap-[--layout-gap-lg]">
          <div>
            <!-- 房間資訊卡 -->
            <div class="bg-blue-950/50 p-4 rounded-lg">
              <h1 class="text-xl">{{ room.title }}</h1>
              <div class="mt-2 text-blue-300">
                <div>成員數：0人</div>
              </div>
            </div>
          </div>

          <div class="hidden md:block">
            <!-- 播放清單卡 -->
            <Playlist
              class="rounded-lg overflow-hidden"
              :current-playing="currentPlaying"
              :playlist-items="playlistItems"
              @select-item="selectedPlaylistItem"
            />
          </div>

          <div class="hidden xl:block">
            <!-- 房間成員卡 -->
            <RoomMembers />
          </div>
        </div>
      </div>

    </div>

    <!-- 手機底部播放清單按鈕層 -->
    <div class="fixed z-10 inset-x-0 bottom-0 max-h-screen flex flex-col backdrop-blur-lg md:hidden">
      <!-- 播放清單卡 -->
      <Transition
        enter-active-class="duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
      >
        <Playlist
          v-if="isOpenMobilePlaylist"
          class="border-t border-blue-900/50 overflow-y-auto"
          :current-playing="currentPlaying"
          :playlist-items="playlistItems"
          @select-item="selectedPlaylistItem"
        />
      </Transition>

      <!-- 手機底部播放清單按鈕 -->
      <div class="bg-gray-950 grow-0">
        <button
          type="button"
          class="w-full p-2 flex justify-between items-center bg-blue-950/50 border-t border-blue-900/50"
          @click="isOpenMobilePlaylist = !isOpenMobilePlaylist"
        >
          <div class="flex items-center">
            <HeroiconsPlayCircle class="w-8 h-8 mr-1" />
            播放清單
          </div>

          <HeroiconsChevronUp
            class="w-6 h-6 transition-transform"
            :class="{ 'rotate-180': !isOpenMobilePlaylist }"
          />
        </button>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { disableBodyScroll, enableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock'
import { Echo, safeListenFn } from '@/echo'
import Player from '@/components/player/Player.vue'
import type { Room, PlaylistItem } from '@/types'

const props = defineProps<{
  room: Required<Room>
  current_playing: PlaylistItem | null
  playlist_items: PlaylistItem[]
}>()

const currentPlaying = ref(props.current_playing) as Ref<PlaylistItem | null>
const playlistItems = ref(props.playlist_items) as Ref<PlaylistItem[]>

const player = ref(null) as Ref<InstanceType<typeof Player> | null>

const isOpenMobilePlaylist = ref(false)

function ended() {
  router.post(`/rooms/${props.room.id}/next`, {
    current_playing_id: props.current_playing?.id,
  })
}

function selectedPlaylistItem(item: PlaylistItem) {
  router.post(`/rooms/${props.room.id}/play/${item.id}`)
}

// 監聽當有其他人切換影片時的事件
function onPlayerlistItemClicked() {
  router.reload()
}

watch(() => props.current_playing, () => {
  currentPlaying.value = props.current_playing
})

watch(() => props.playlist_items, () => {
  playlistItems.value = props.playlist_items
})

watch(isOpenMobilePlaylist, isOpenMobilePlaylist => {
  if (isOpenMobilePlaylist) {
    disableBodyScroll(document.body)
  } else {
    enableBodyScroll(document.body)
  }
})

watch(player, (v, ov, invalidate) => {
  // 如果當前有播放影片，就要擋掉第一次監聽，因為 `player` 還沒載入。
  // 但如果是沒有播放，就可以註冊，因為要監聽其他人切換影片時的事件。
  if (currentPlaying.value && !player.value) return

  Echo.join(`player.${props.room.id}`)
    .listen('PlayerPlayed', safeListenFn(player.value?.onPlayerPlayed))
    .listen('PlayerPaused', safeListenFn(player.value?.onPlayerPaused))
    .listen('PlayerSeeked', safeListenFn(player.value?.onPlayerSeeked))
    .listen('PlayerlistItemClicked', onPlayerlistItemClicked)

  invalidate(() => {
    Echo.leave(`player.${props.room.id}`)
  })
}, { immediate: true })

onBeforeUnmount(() => {
  clearAllBodyScrollLocks()
})
</script>
