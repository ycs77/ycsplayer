<template>
  <div class="px-[--layout-gap] pb-[calc(var(--layout-gap)+3rem)] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">
      <!-- 導覽列 -->
      <RoomNavbar
        class="col-span-12"
        :room-id="room.id"
        :can-upload-medias="can.uploadMedias"
        :can-settings="can.settings"
      />

      <div class="col-span-12 md:col-span-8 lg:col-span-9">
        <div class="relative">
          <!-- 播放器 -->
          <div v-if="currentPlaying" class="rounded-lg overflow-hidden">
            <Player
              ref="player"
              :key="currentPlaying.id"
              :room-id="room.id"
              :src="currentPlaying.url"
              :type="currentPlaying.type"
              :poster="currentPlaying.preview ?? undefined"
              :autoplay="room.auto_play"
              @end="ended"
            />
          </div>

          <!-- 無播放項目時的提示 -->
          <div v-else>
            <div class="flex justify-center items-center bg-blue-950/50 text-blue-300 text-lg rounded-lg aspect-video">
              請選擇播放項目
            </div>
          </div>

          <PlayerDebugger v-if="debug" :room-id="room.id" />
        </div>
      </div>

      <div class="col-span-12 md:col-span-4 lg:col-span-3">
        <div class="grid gap-[--layout-gap] lg:gap-[--layout-gap-lg]">
          <div>
            <!-- 房間資訊卡 -->
            <RoomStatusCard
              :room="room"
              :members-count="members.length"
              :editing-user="editingUser"
            />
          </div>

          <div class="hidden md:block">
            <!-- 播放清單卡 -->
            <Playlist
              class="rounded-lg overflow-hidden"
              :current-playing="currentPlaying"
              :playlist-items="playlistItems"
              :can-add="can.operatePlaylistItem"
              :can-remove="can.operatePlaylistItem"
              @open-add-item="openAddPlaylistItemModal"
              @select-item="selectPlaylistItem"
              @remove-item="removePlaylistItem"
            />
          </div>

          <div class="hidden xl:block">
            <!-- 房間成員卡 -->
            <RoomMembers
              :members="members"
              :room-id="room.id"
              :can-invite="can.inviteMember"
              :can-change-role="can.changeMemberRole"
              :can-remove="can.removeMember"
            />
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
          v-if="showMobilePlaylist"
          ref="mobilePlaylist"
          class="max-h-[50vh] border-t border-blue-900/50 overflow-y-auto"
          :current-playing="currentPlaying"
          :playlist-items="playlistItems"
          :can-add="can.operatePlaylistItem"
          :can-remove="can.operatePlaylistItem"
          @open-add-item="openAddPlaylistItemModal"
          @select-item="selectPlaylistItem"
          @remove-item="removePlaylistItem"
        />
      </Transition>

      <!-- 手機底部播放清單按鈕 -->
      <div class="bg-gray-950 grow-0">
        <button
          type="button"
          class="w-full p-3 flex justify-between items-center bg-blue-950/50 border-t border-blue-900/50"
          @click="showMobilePlaylist = !showMobilePlaylist"
        >
          <div class="flex items-center">
            <HeroiconsPlayCircle class="w-8 h-8 mr-1" />
            播放清單
          </div>

          <HeroiconsChevronUp
            class="w-6 h-6 transition-transform"
            :class="{
              '-rotate-0': showMobilePlaylist,
              '-rotate-180': !showMobilePlaylist,
            }"
          />
        </button>
      </div>
    </div>

    <RoomAddPlaylistItemModal
      v-model="showAddPlaylistItemModal"
      :form="playlistItemForm"
      :medias="medias"
      :submitting="playlistItemForm.processing"
      @submit="submitPlaylistItemForm"
    />
  </div>
</template>

<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'
import { clearAllBodyScrollLocks, disableBodyScroll, enableBodyScroll } from 'body-scroll-lock'
import { Echo, safeListenFn } from '@/echo'
import Player from '@/components/player/Player.vue'
import Playlist from '@/components/player/Playlist.vue'
import { type Media, PlayerType, type PlaylistItem, type PlaylistItemForm, type Room, type RoomMember, RoomType } from '@/types'

const props = defineProps<{
  room: Required<Room>
  debug: boolean
  currentPlaying: PlaylistItem | null
  playlistItems: PlaylistItem[]
  editingUser: {
    id: string
    name: string
  } | null
  medias: Media[]
  members: RoomMember[]
  can: {
    operatePlaylistItem: boolean
    inviteMember: boolean
    changeMemberRole: boolean
    removeMember: boolean
    uploadMedias: boolean
    settings: boolean
  }
}>()

const player = ref(null) as Ref<InstanceType<typeof Player> | null>
const mobilePlaylist = ref(null) as Ref<InstanceType<typeof Playlist> | null>

const showAddPlaylistItemModal = ref(false)
const showMobilePlaylist = ref(false)

const playlistItemForm = useForm({
  type: PlayerType.Video,
  title: '',
  url: '',
  media_id: null,
}) as InertiaForm<PlaylistItemForm>

function ended() {
  router.post(`/rooms/${props.room.id}/next`, {
    current_playing_id: props.currentPlaying?.id,
  })
}

function selectPlaylistItem(item: PlaylistItem) {
  showMobilePlaylist.value = false
  router.post(`/rooms/${props.room.id}/playlist/${item.id}`, {}, {
    only: [...globalOnly, 'currentPlaying', 'playlistItems'],
    preserveScroll: true,
  })
}

function removePlaylistItem(item: PlaylistItem) {
  router.delete(`/rooms/${props.room.id}/playlist/${item.id}`, {
    only: [
      ...globalOnly,
      ...(item.id === props.currentPlaying?.id
        ? ['currentPlaying']
        : []),
      'playlistItems',
    ],
    preserveScroll: true,
  })
}

function openAddPlaylistItemModal() {
  playlistItemForm.type = props.room.type === RoomType.Audio
    ? PlayerType.Audio
    : PlayerType.Video

  if (props.playlistItems.length) {
    playlistItemForm.type = props.playlistItems.slice(-1)[0].type
  }

  playlistItemForm.title = ''
  playlistItemForm.url = ''
  playlistItemForm.media_id = null

  showAddPlaylistItemModal.value = true
}

function submitPlaylistItemForm(form: PlaylistItemForm) {
  playlistItemForm.type = form.type
  playlistItemForm.title = form.title
  playlistItemForm.url = form.url
  playlistItemForm.media_id = form.media_id

  playlistItemForm.post(`/rooms/${props.room.id}/playlist`, {
    only: [...globalOnly, 'playlistItems'],
    preserveScroll: true,
    onSuccess() {
      showAddPlaylistItemModal.value = false
    },
  })
}

// 監聽當有其他人新增播放項目時的事件
function onPlayerlistItemAdded() {
  router.reload({
    only: [...globalOnly, 'playlistItems'],
  })
}

// 監聽當有其他人點擊指定播放影片時的事件
function onPlayerlistItemClicked() {
  router.reload({
    only: [...globalOnly, 'currentPlaying', 'playlistItems'],
  })
}

// 監聽當有其他人切換下一部播放影片時的事件
function onPlayerlistItemNexted() {
  router.reload({
    only: [...globalOnly, 'currentPlaying', 'playlistItems'],
  })
}

// 監聽當有其他人刪除待播影片(不是當前播放)時的事件
function onPlayerlistItemRemoved() {
  router.reload({
    only: [...globalOnly, 'playlistItems'],
  })
}

// 監聽當有其他人開始更新記事本時的事件
function onNoteUpdating() {
  router.reload({
    only: [...globalOnly, 'editingUser'],
  })
}

// 監聽當有其他人更新了記事本時的事件
function onNoteUpdated() {
  router.reload({
    only: [...globalOnly, 'room', 'editingUser'],
  })
}

// 監聽當有其他人取消更新記事本時的事件
function onNoteCanceled() {
  router.reload({
    only: [...globalOnly, 'editingUser'],
  })
}

// 監聽當上傳並轉換完成檔案時的事件
function onRoomMediaConverted() {
  router.reload({
    only: [...globalOnly, 'medias'],
  })
}

// 監聽當刪除檔案時的事件
function onRoomMediaRemoved() {
  router.reload({
    only: [...globalOnly, 'medias'],
  })
}

// 監聽當有其他人上線或離線時的事件
function onOnlineMembersUpdated() {
  router.reload({
    only: [...globalOnly, 'members'],
  })
}

watch(showMobilePlaylist, showMobilePlaylist => {
  if (showMobilePlaylist) {
    nextTick(() => {
      mobilePlaylist.value?.scrollIntoCurrentItem()
    })

    disableBodyScroll(document.body)
  } else {
    enableBodyScroll(document.body)
  }
})

watch(player, (v, ov, onInvalidate) => {
  // 如果當前有播放影片，就要擋掉第一次監聽，因為 `player` 還沒載入。
  // 但如果是沒有播放，就可以註冊，因為要監聽其他人切換影片時的事件。
  if (props.currentPlaying && !player.value) return

  Echo.join(`player.${props.room.id}`)
    .listen('PlayerPlayed', safeListenFn(player.value?.onPlayerPlayed))
    .listen('PlayerPaused', safeListenFn(player.value?.onPlayerPaused))
    .listen('PlayerSeeked', safeListenFn(player.value?.onPlayerSeeked))
    .listen('PlayerlistItemAdded', onPlayerlistItemAdded)
    .listen('PlayerlistItemClicked', onPlayerlistItemClicked)
    .listen('PlayerlistItemNexted', onPlayerlistItemNexted)
    .listen('PlayerlistItemRemoved', onPlayerlistItemRemoved)
    .listen('RoomNoteUpdating', onNoteUpdating)
    .listen('RoomNoteUpdated', onNoteUpdated)
    .listen('RoomNoteCanceled', onNoteCanceled)
    .listen('RoomMediaConverted', onRoomMediaConverted)
    .listen('RoomMediaRemoved', onRoomMediaRemoved)
    .listen('RoomOnlineMembersUpdated', onOnlineMembersUpdated)

  onInvalidate(() => {
    Echo.leave(`player.${props.room.id}`)
  })
}, { immediate: true })

onBeforeUnmount(() => {
  clearAllBodyScrollLocks()
})
</script>
