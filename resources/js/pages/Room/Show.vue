<template>
  <div class="px-[--layout-gap] pb-[calc(var(--layout-gap)+3.5rem)] md:pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg] h-full">
    <div class="flex flex-col gap-[--layout-gap] md:grid md:grid-cols-12 lg:gap-[--layout-gap-lg] h-full">
      <div class="min-h-0 shrink-0 md:col-span-8 lg:col-span-9">
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
              :operate="can.operatePlayer"
              :force-play-from-start="forcePlayFromStart"
              :wait-other-players="shouldWaitOtherPlayers"
              @play="onPlayerPlayed"
              @pause="onPlayerPaused"
              @seek="onPlayerSeeked"
              @next="onPlayerNext"
              @end="onPlayerEnd"
            />
          </div>

          <!-- 無播放項目時的提示 -->
          <div v-else>
            <div class="flex justify-center items-center bg-blue-950/50 text-blue-300 text-lg text-center rounded-lg aspect-video">
              <span class="md:hidden">請點選下方播放清單<br>新增播放項目 或 選擇播放項目</span>
              <span class="hidden md:inline">請新增播放項目 或 選擇播放項目</span>
            </div>
          </div>

          <PlayerDebugger v-if="room.debug" />
        </div>
      </div>

      <div class="grow min-h-0 flex flex-col gap-y-[--layout-gap] md:col-span-4 lg:col-span-3">
        <!-- 導覽列 -->
        <RoomTabs
          v-model:tab="tab"
          :chat-unread="chatUnread"
          :can-upload-medias="can.uploadMedias"
          :can-settings="can.settings"
          class="shrink-0"
        />

        <div class="grow min-h-0 flex flex-col gap-y-[--layout-gap] overflow-y-auto">
          <!-- 首頁分頁 -->
          <template v-if="tab === 'main'">
            <div>
              <!-- 房間資訊卡 -->
              <RoomStatusCard
                :room="room"
                :members-count="members.length"
                :editing-user="editingUser"
                :can-edit-note="can.editNote"
              />
            </div>

            <!-- 房間成員卡 -->
            <RoomMembers
              :members="members"
              :room-id="room.id"
              :can-invite="can.inviteMember"
              :can-change-role="can.changeMemberRole"
              :can-remove="can.removeMember"
            />

            <!-- 播放清單卡 -->
            <Playlist
              :current-playing="currentPlaying"
              :playlist-items="playlistItems"
              :can-add="can.operatePlaylistItem"
              :can-select="can.operatePlaylistItem"
              :can-remove="can.operatePlaylistItem"
              class="hidden md:block rounded-lg overflow-y-auto min-h-0"
              @click-add-item="openAddPlaylistItemModal"
              @select-item="selectPlaylistItem"
              @remove-item="removePlaylistItem"
            />
          </template>

          <!-- 聊天室分頁 -->
          <RoomChat
            v-else-if="tab === 'chat'"
            :messages="chatMessages"
            class="grow min-h-0"
            @mark-all-read="MarkAllChatMessagesAsRead"
            @send-message="sendChatMessage"
          />

          <!-- 檔案分頁 -->
          <RoomMedias
            v-else-if="tab === 'medias'"
            :room="room"
            :medias="[...medias, ...loadingMedias]"
            :csrf-token="csrfToken"
            class="overflow-y-auto min-h-0"
            @uploaded="onMediaUpload"
            @delete-media="onMediaDeleting"
          />

          <!-- 設定分頁 -->
          <RoomSettings
            v-else-if="tab === 'settings'"
            :room="room"
            :can-delete-room="can.delete"
          />
        </div>
      </div>
    </div>

    <!-- 手機底部播放清單按鈕層 -->
    <div class="md:hidden">
      <div
        v-if="showMobilePlaylist"
        class="fixed inset-0 z-20"
        @click="showMobilePlaylist = false"
      />

      <div class="fixed inset-x-0 bottom-0 z-20 max-h-screen flex flex-col backdrop-blur-lg">
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
            button-on-bottom
            :can-add="can.operatePlaylistItem"
            :can-select="can.operatePlaylistItem"
            :can-remove="can.operatePlaylistItem"
            @click-add-item="openAddPlaylistItemModal"
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
    </div>

    <RoomAddPlaylistItemModal
      v-model="showAddPlaylistItemModal"
      :room-id="room.id"
      :form="playlistItemForm"
      :medias="medias.filter(media => !media.converting)"
      :submitting="playlistItemForm.processing"
      @submit="submitPlaylistItemForm"
    />
  </div>
</template>

<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { clearAllBodyScrollLocks, disableBodyScroll, enableBodyScroll } from 'body-scroll-lock'
import { Echo, type PresenceChannel, safeListenFn } from '@/echo'
import Player from '@/components/player/Player.vue'
import Playlist from '@/components/player/Playlist.vue'
import type { Media, PlayerPausedEvent, PlayerPlayedEvent, PlayerSeekedEvent, PlaylistItem, PlaylistItemForm, Room, RoomChannelMember, RoomChatMessage, RoomMediaConvertedEvent, RoomMember } from '@/types'
import { PlayerTrigger, PlayerType, RoomType } from '@/types'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  room: Required<Room>
  csrfToken: string
  currentPlaying: PlaylistItem | null
  playlistItems: PlaylistItem[]
  editingUser: {
    id: string
    name: string
  } | null
  medias: Media[]
  loadingMedias: Media[]
  members: RoomMember[]
  can: {
    operatePlayer: boolean
    operatePlaylistItem: boolean
    editNote: boolean
    inviteMember: boolean
    changeMemberRole: boolean
    removeMember: boolean
    uploadMedias: boolean
    settings: boolean
    delete: boolean
  }
}>()

useFullPage(true, {
  moreClass: [
    'max-h-full',
    'min-h-[600px]',
    'max-md:landscape:min-h-[800px]', // 手機版橫式時套用
    'lg:min-h-[700px]',
  ],
})

let channel: PresenceChannel | undefined

const player = ref(null) as Ref<InstanceType<typeof Player> | null>
const mobilePlaylist = ref(null) as Ref<InstanceType<typeof Playlist> | null>

const showAddPlaylistItemModal = ref(false)
const showMobilePlaylist = ref(false)

const trigger = ref(PlayerTrigger.Normal)
const forcePlayFromStart = computed(() => [PlayerTrigger.Click, PlayerTrigger.Next].includes(trigger.value))

const shouldWaitOtherPlayers = ref(false)

const tab = ref('main')

const { user: authUser } = useAuth()

const toast = useToast()

const playlistItemForm = useForm({
  type: PlayerType.YouTube,
  title: '',
  url: '',
  media_id: null,
}) as InertiaForm<PlaylistItemForm>

const chatMessages = ref([]) as Ref<RoomChatMessage[]>
const chatUnread = computed(() => chatMessages.value.some(message => !message.read))

usePlayerLogger(() => props.room.debug)

// 廣播播放事件
function onPlayerPlayed(e: PlayerPlayedEvent) {
  if (props.can.operatePlayer) {
    channel?.whisper('play', e)
  }
}

// 廣播暫停事件
function onPlayerPaused(e: PlayerPausedEvent) {
  if (props.can.operatePlayer) {
    channel?.whisper('pause', e)
  }
}

// 廣播拖曳進度條事件
function onPlayerSeeked(e: PlayerSeekedEvent) {
  if (props.can.operatePlayer) {
    channel?.whisper('seek', e)
  }
}

// 點擊播放下一首按鈕事件
function onPlayerNext() {
  nextPlaylistItem()
}

// 結束播放事件
function onPlayerEnd() {
  if (props.room.auto_play) {
    nextPlaylistItem()
  }
}

function nextPlaylistItem() {
  router.post(`/rooms/${props.room.id}/next`, {
    current_playing_id: props.currentPlaying?.id,
  }, {
    only: [...globalOnly, 'currentPlaying', 'playlistItems'],
    preserveScroll: true,
    onSuccess() {
      trigger.value = PlayerTrigger.Next
    },
  })
}

function selectPlaylistItem(item: PlaylistItem) {
  showMobilePlaylist.value = false
  router.post(`/rooms/${props.room.id}/playlist/${item.id}`, {}, {
    only: [...globalOnly, 'currentPlaying', 'playlistItems'],
    preserveScroll: true,
    onSuccess() {
      trigger.value = PlayerTrigger.Click
    },
  })
}

function removePlaylistItem(item: PlaylistItem) {
  const isRemoveCurrentPlaying = item.id === props.currentPlaying?.id

  router.delete(`/rooms/${props.room.id}/playlist/${item.id}`, {
    only: [
      ...globalOnly,
      ...(isRemoveCurrentPlaying ? ['currentPlaying'] : []),
      'playlistItems',
    ],
    preserveScroll: true,
    onSuccess() {
      if (isRemoveCurrentPlaying) {
        trigger.value = PlayerTrigger.Next
      }
    },
  })
}

function openAddPlaylistItemModal() {
  if (props.playlistItems.length) {
    playlistItemForm.type = props.playlistItems.slice(-1)[0].type
  } else if (props.room.type === RoomType.Audio) {
    playlistItemForm.type = PlayerType.Audio
  } else {
    playlistItemForm.type = PlayerType.YouTube
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

function sendChatMessage(message: RoomChatMessage) {
  chatMessages.value.push(message)

  channel?.whisper('chat', message)
}

function MarkAllChatMessagesAsRead() {
  chatMessages.value
    .filter(message => !message.read)
    .forEach(message => message.read = true)
}

function onMediaUpload(message: string | null) {
  if (message) {
    toast.success(message)
  }
}

function onMediaDeleting(media: Media) {
  if (confirm(`確定要刪除 ${media.name} 嗎?`)) {
    router.delete(`/rooms/${props.room.id}/medias/${media.id}`, {
      only: [...globalOnly, 'csrfToken', 'currentPlaying', 'playlistItems', 'medias'],
      preserveScroll: true,
    })
  }
}

function onCurrentMemberJoining(users: RoomChannelMember[]) {
  const otherUsers = users.filter(user => user.id !== authUser.value?.id)
  if (otherUsers.length > 0) {
    shouldWaitOtherPlayers.value = true
  }
}

function onMemberJoining(user: RoomChannelMember) {
  if (!player.value) return
  if (!player.value.isClickedBigButton()) return
  channel?.whisper('currenttime', {
    user,
    paused: player.value.paused(),
    currentTime: player.value.currentTime(),
    timestamp: player.value.toServerTimestamp(Date.now()),
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
    onSuccess() {
      trigger.value = PlayerTrigger.Click
    },
  })
}

// 監聽當有其他人切換下一部播放影片時的事件
function onPlayerlistItemNexted() {
  router.reload({
    only: [...globalOnly, 'currentPlaying', 'playlistItems'],
    onSuccess() {
      trigger.value = PlayerTrigger.Next
    },
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

// 監聽當檔案上傳完成檔時的事件
function onRoomMediaUploaded() {
  router.reload({
    only: [...globalOnly, 'csrfToken', 'medias', 'loadingMedias'],
  })
}

// 監聽當檔案上傳並轉換完成時的事件
function onRoomMediaConverted(e: RoomMediaConvertedEvent) {
  router.reload({
    only: [...globalOnly, 'medias', 'loadingMedias'],
    onSuccess() {
      toast.success(e.message)
    },
  })
}

// 監聽當有其他人上線或離線時的事件
function onOnlineMembersUpdated() {
  router.reload({
    only: [...globalOnly, 'members'],
  })
}

// 監聽當更新當前播放進度的事件
class TimeLock {
  threshold = 1500
  expiredTimestamp: number = Date.now()
  constructor(protected items: number[] = []) {
    //
  }

  has(timestamp: number) {
    return this.items.some(_timestamp =>
      _timestamp >= timestamp - this.threshold &&
      _timestamp <= timestamp + this.threshold
    )
  }

  prune() {
    this.items = this.items.filter(_timestamp => _timestamp <= this.expiredTimestamp)
  }
}
const currentTimeLock = new TimeLock()
currentTimeLock.expiredTimestamp = Date.now() - 10 * 1000 // 10秒前的就算過期

function onClientUpdateCurrentTime({ user, paused, currentTime, timestamp }: {
  user: RoomChannelMember
  paused: boolean
  currentTime: number
  timestamp: number
}) {
  if (user.id !== authUser.value?.id) return
  if (currentTimeLock.has(timestamp)) return

  // 清除過期的數值
  currentTimeLock.prune()

  player.value?.onOtherPlayerTimeUpdate({ paused, currentTime, timestamp })
}

function onClientChatMessageSent(message: RoomChatMessage) {
  chatMessages.value.push(message)
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

watch(player, (player, _, onInvalidate) => {
  // 如果當前有播放影片，就要擋掉第一次監聽，因為 `player` 還沒載入。
  // 但如果是沒有播放，就可以註冊，因為要監聽其他人切換影片時的事件。
  if (props.currentPlaying && !player) return

  channel = Echo.join(`player.${props.room.id}`)
  channel.here(onCurrentMemberJoining)
  channel.joining(onMemberJoining)
  channel.listen('PlayerlistItemAdded', onPlayerlistItemAdded)
  channel.listen('PlayerlistItemClicked', onPlayerlistItemClicked)
  channel.listen('PlayerlistItemNexted', onPlayerlistItemNexted)
  channel.listen('PlayerlistItemRemoved', onPlayerlistItemRemoved)
  channel.listen('RoomNoteUpdating', onNoteUpdating)
  channel.listen('RoomNoteUpdated', onNoteUpdated)
  channel.listen('RoomNoteCanceled', onNoteCanceled)
  channel.listen('RoomMediaUploaded', onRoomMediaUploaded)
  channel.listen('RoomMediaConverted', onRoomMediaConverted)
  channel.listen('RoomOnlineMembersUpdated', onOnlineMembersUpdated)
  channel.listenForWhisper('play', safeListenFn(player?.onOtherPlayerPlayed))
  channel.listenForWhisper('pause', safeListenFn(player?.onOtherPlayerPaused))
  channel.listenForWhisper('seek', safeListenFn(player?.onOtherPlayerSeeked))
  channel.listenForWhisper('currenttime', onClientUpdateCurrentTime)
  channel.listenForWhisper('chat', onClientChatMessageSent)

  onInvalidate(() => {
    channel = undefined
    Echo.leave(`player.${props.room.id}`)
  })
}, { immediate: true })

onBeforeUnmount(() => {
  clearAllBodyScrollLocks()
})
</script>
