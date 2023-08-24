<template>
  <video-js
    ref="videoRef"
    :class="{ 'vjs-youtube-block-touch': type === PlayerType.YouTube }"
  />
</template>

<script setup lang="ts">
import axios from 'axios'
import { throttle, debounce } from 'lodash-es'
import { promiseTimeout } from '@vueuse/core'
import 'video.js/dist/video-js.css'
import videojs from 'video.js'
import type Player from 'video.js/dist/types/player'
import type Component from 'video.js/dist/types/component'
import type PosterImage from 'video.js/dist/types/poster-image'
import type BigPlayButton from 'video.js/dist/types/big-play-button'
import type PlayToggle from 'video.js/dist/types/control-bar/play-toggle'
import type SeekBar from 'video.js/dist/types/control-bar/progress-control/seek-bar'
import 'videojs-youtube'
import { Echo } from '@/echo'
import { PlayerType, type PlayerPlayedEvent, type PlayerPausedEvent, type PlayerSeekedEvent } from '@/types'

const props = defineProps<{
  roomId: number
  src: string
  type: PlayerType
  poster?: string
  autoplay?: boolean
}>()

const emit = defineEmits<{
  ended: []
}>()

const videoRef = ref() as Ref<HTMLVideoElement>

let player: Player | undefined
let playCallback = null as (() => void) | null
let is_ended = false

function isClickedBigButton() {
  return player?.hasStarted_
}

function play(handler?: () => void, checkPaused = true) {
  if (!player) return
  if (checkPaused && !player.paused()) return

  const clickedBigButton = isClickedBigButton()

  playCallback = () => {
    handler?.()
  }

  axios.post('/player/play', {
    room_id: props.roomId,
    timestamp: Date.now(),
    current_time: clickedBigButton
      ? Math.round(player.currentTime() * 100) / 100
      : null,
    is_clicked_big_button: clickedBigButton,
  }).then(() => {
    if (!player) return

    // 如果 5秒 後還沒有回應，就開始播放當前使用者
    setTimeout(() => {
      if (typeof playCallback === 'function' && player?.paused()) {
        playCallback()
        playCallback = null
      }
    }, 5000)
  })
}

function pause(handler?: () => void) {
  if (!player) return
  if (player.paused()) return

  handler?.()

  axios.post('/player/pause', {
    room_id: props.roomId,
    current_time: Math.round(player.currentTime() * 100) / 100,
  }).then()
}

function seeked() {
  if (!player) return

  axios.post('/player/seeked', {
    room_id: props.roomId,
    timestamp: Date.now(),
    current_time: Math.round(player.currentTime() * 100) / 100,
    paused: player.paused(),
  }).then()
}

const timeUpdate = throttle(() => {
  if (!player) return
  if (is_ended) return

  axios.post('/player/time-update', {
    room_id: props.roomId,
    timestamp: Date.now(),
    current_time: Math.round(player.currentTime() * 100) / 100,
    paused: player.paused(),
  }).then()
}, 1000 * 5)

function end() {
  if (!player) return

  is_ended = true

  axios.post('/player/end', {
    room_id: props.roomId,
  }).then(() => {
    emit('ended')
  })
}

onMounted(() => {
  videojs.log.level('off')

  const sourceType =
    props.type === PlayerType.YouTube ? 'video/youtube' :
    props.type === PlayerType.Audio ? 'audio/mpeg' :
    'video/mp4'

  const videojsOptions = {
    controls: true,
    fluid: true,
    sources: [{
      src: props.src,
      type: sourceType,
    }],
  } as Record<string, any>

  if (props.poster) {
    videojsOptions.poster = props.poster
  }

  if (props.type === PlayerType.YouTube) {
    videojsOptions.techOrder = ['youtube']
    videojsOptions.youtube = {
      hl: navigator.language,
      origin: location.origin,
      disablekb: 1,
    }
  }

  player = videojs(videoRef.value, videojsOptions)

  player.on('timeupdate', timeUpdate)
  player.on('ended', end)

  player.ready(function() {
    if (!player) return

    if (props.autoplay) {
      play(() => {
        silencePromise(player?.play())
      })
    }
  })

  class YcsPosterImage extends (videojs.getComponent('PosterImage') as unknown as {
    new (player: Player, options?: any): PosterImage
  }) {
    handleClick(event: Event) {
      if (!this.player_.controls()) {
        return
      }

      if (this.player_.tech(true)) {
        this.player_.tech(true).focus()
      }

      if (this.player_.paused()) {
        this.player_.handleTechWaiting_()
        silencePromise(this.player_.play())
        play(() => {
          this.player_?.play()
        }, false)
      } else {
        this.player_.pause()
      }
    }
  }

  class YcsBigPlayButton extends (videojs.getComponent('BigPlayButton') as unknown as {
    new (player: Player, options?: any): BigPlayButton
  }) {
    handleClick(event: KeyboardEvent) {
      this.player_.handleTechWaiting_()
      super.handleClick(event)
      play(() => {
        if (this.player_)
          super.handleClick(event)
      }, false)
    }
  }

  class YcsPlayToggle extends (videojs.getComponent('PlayToggle') as unknown as {
    new (player: Player, options?: any): PlayToggle
  }) {
    handleClick(event: Event) {
      if (this.player_.paused()) {
        this.player_.handleTechWaiting_()
        play(() => {
          this.player_?.play()
        })
      } else {
        pause(() => {
          this.player_?.pause()
        })
      }
    }
  }

  class YcsSeekBar extends (videojs.getComponent('SeekBar') as unknown as {
    new (player: Player, options?: any): SeekBar
  }) {
    onSekked_: () => void

    constructor(player: Player, options?: any) {
      super(player, options)

      this.onSekked_ = debounce(seeked, 100)
    }

    handleMouseUp(event: MouseEvent) {
      super.handleMouseUp(event)
      this.onSekked_()
    }
  }

  const posterImage = player.getChild('posterImage')!
  const bigPlayButton = player.getChild('bigPlayButton')!
  const controlBar = player.getChild('controlBar')!
  const playToggle = controlBar.getChild('playToggle')!
  const volumePanel = controlBar.getChild('volumePanel')!
  const pictureInPictureToggle = controlBar.getChild('pictureInPictureToggle')!
  const progressControl = controlBar.getChild('ProgressControl')!
  const seekBar = progressControl.getChild('SeekBar')!
  const playProgressBar = seekBar.getChild('PlayProgressBar')!
  const timeTooltip = playProgressBar.getChild('TimeTooltip')
  const hasTimeTooltip = typeof timeTooltip !== undefined

  player.removeChild(posterImage)
  player.removeChild(bigPlayButton)
  controlBar.removeChild(playToggle)
  controlBar.removeChild(volumePanel)
  controlBar.removeChild(pictureInPictureToggle)
  if (hasTimeTooltip && timeTooltip) {
    playProgressBar.removeChild(timeTooltip)
  }
  progressControl.removeChild(seekBar)

  videojs.registerComponent('PosterImage', YcsPosterImage as unknown as Component)
  videojs.registerComponent('BigPlayButton', YcsBigPlayButton as unknown as Component)
  videojs.registerComponent('PlayToggle', YcsPlayToggle as unknown as Component)
  videojs.registerComponent('SeekBar', YcsSeekBar as unknown as Component)

  player.addChild('PosterImage', {}, 1)
  player.addChild('BigPlayButton', {}, 2)
  controlBar.addChild('PlayToggle', {}, 0)
  progressControl.addChild('SeekBar', {}, 0)
})

// 監聽播放事件
function onPlayerPlayed(e: PlayerPlayedEvent) {
  if (!player) return

  // 如果觸發的播放器是正在點擊 bigPlayButton，
  // 同時當前播放器已經點擊過 bigPlayButton，
  // 同時觸發的播放器不是當前播放器，
  // 就不要執行播放。
  if (!e.status.is_clicked_big_button &&
      isClickedBigButton() &&
      e.socketId !== Echo.socketId()
  ) return

  is_ended = false

  let currentTime = e.status.current_time ?? 0

  // 如果現在觸發的不是第一個開始播放的，就要校正播放時間。(觸發的當前播放器，正在點擊 bigPlayButton)
  //
  // 但需要注意：如果是播放中同時開啟兩個播放器會正常，但是如果播放一段時間後關閉，
  // 再過一會兒重開播放器會發現時間跳過了一段時間，這是因為下面這段的關係。
  // 解決此問題是使用 Pusher 的 Webhook 功能，可以查看
  // `app/Broadcasting/Http/Controllers/PusherWebhookController.php`
  if (!e.isFirst &&
      !e.status.is_clicked_big_button &&
      typeof e.status.current_time === 'number' &&
      e.socketId === Echo.socketId()
  ) {
    const seconds = (Date.now() - e.status.timestamp) / 1000
    currentTime = Math.round((e.status.current_time + seconds) * 100) / 100
  }

  if (currentTime < player.duration()) {
    player.currentTime(currentTime)
  }

  if (import.meta.env.DEV) {
    console.log('PlayerPlayed', currentTime)
  }

  if (typeof playCallback === 'function') {
    playCallback()
    playCallback = null
  } else {
    silencePromise(player.play())
  }
}

// 監聽暫停事件
function onPlayerPaused(e: PlayerPausedEvent) {
  if (!player) return

  if (import.meta.env.DEV) {
    console.log('PlayerPaused', e.status.current_time)
  }

  if (typeof e.status.current_time === 'number') {
    player.currentTime(e.status.current_time)
  }

  if (!player.paused()) {
    player.pause()
  }
}

// 監聽改變進度條事件 (只發布給其他播放器)
function onPlayerSeeked(e: PlayerSeekedEvent) {
  if (!player) return

  if (import.meta.env.DEV) {
    console.log('PlayerSeeked', e.status.current_time)
  }

  if (typeof e.status.timestamp === 'number' &&
      typeof e.status.current_time === 'number'
  ) {
    const seconds = (Date.now() - e.status.timestamp) / 1000
    player.currentTime(Math.round((e.status.current_time + seconds) * 100) / 100)
  }

  // 如果是[播放]但當前使用者是[暫停]，就要執行[播放]
  if (!e.status.paused && player.paused()) {
    silencePromise(player.play())
  }

  // 如果是[暫停]但當前使用者是[播放]，就要執行[暫停]
  if (e.status.paused && !player.paused()) {
    player.pause()
  }
}

onBeforeUnmount(async () => {
  if (player) {
    player.pause()
    await promiseTimeout(100)

    player.off('timeupdate', timeUpdate)
    player.off('ended', end)
    await promiseTimeout(100)

    player.dispose()
  }
})

defineExpose({
  onPlayerPlayed,
  onPlayerPaused,
  onPlayerSeeked,
})
</script>

<style scoped>
:deep(.vjs-youtube.vjs-youtube-block-touch iframe) {
  pointer-events: none !important;
}
</style>
