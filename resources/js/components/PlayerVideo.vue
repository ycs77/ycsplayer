<template>
  <div class="relative max-w-[600px]">
    <video-js ref="videoRef" class="vjs-youtube-block-touch" />
  </div>
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
  src: string
  type: PlayerType
  poster?: string
}>()

const videoRef = ref() as Ref<HTMLVideoElement>

let player: Player | undefined
let playCallback = null as (() => void) | null
let is_ended = false

function play(handler?: () => void) {
  if (!player) return
  if (!player.paused()) return

  is_ended = false

  const hasStarted = player.hasStarted_

  playCallback = () => {
    handler?.()
  }

  axios.post('/player/play', {
    player_type: props.type,
    timestamp: hasStarted ? Date.now() : null,
    is_started: hasStarted,
    current_time: hasStarted
      ? Math.round(player.currentTime() * 100) / 100
      : null,
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
    player_type: props.type,
    timestamp: Date.now(),
    current_time: Math.round(player.currentTime() * 100) / 100,
  }).then()
}

function seeked() {
  if (!player) return

  axios.post('/player/seeked', {
    player_type: props.type,
    timestamp: Date.now(),
    current_time: Math.round(player.currentTime() * 100) / 100,
    paused: player.paused(),
  }, {
    headers: {
      'X-Socket-ID': Echo.socketId(),
    },
  }).then()
}

const timeUpdate = throttle(() => {
  if (!player) return
  if (is_ended) return

  axios.post('/player/time-update', {
    player_type: props.type,
    timestamp: Date.now(),
    current_time: Math.round(player.currentTime() * 100) / 100,
    paused: player.paused(),
  }).then()
}, 1000 * 5)

function end() {
  if (!player) return

  is_ended = true

  axios.post('/player/end', {
    player_type: props.type,
  }).then()
}

onMounted(() => {
  videojs.log.level('off')

  const sourceType = props.type === PlayerType.YouTube
    ? 'video/youtube'
    : 'video/mp4'

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
        play(() => {
          silencePromise(this.player_.play())
        })
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
      play(() => {
        super.handleClick(event)
      })
    }
  }

  class YcsPlayToggle extends (videojs.getComponent('PlayToggle') as unknown as {
    new (player: Player, options?: any): PlayToggle
  }) {
    handleClick(event: Event) {
      if (this.player_.paused()) {
        this.player_.handleTechWaiting_()
        play(() => {
          this.player_.play()
        })
      } else {
        pause(() => {
          this.player_.pause()
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

  Echo.join(`player.${props.type}`)
    // 監聽播放事件
    .listen('PlayerPlayed', (e: PlayerPlayedEvent) => {
      if (!player) return

      // 如果觸發的播放器是正在點擊 bigPlayButton，
      // 同時當前播放器已經點擊過 bigPlayButton，
      // 就不要執行播放。
      if (!e.status.is_started && player.hasStarted_) return

      if (import.meta.env.DEV) {
        console.log('PlayerPlayed', e.status.current_time)
      }

      let currentTime = e.status.current_time

      // 如果觸發的當前播放器，正在點擊 bigPlayButton，
      // 就要校正播放時間。
      if (!e.status.is_started &&
          !player.hasStarted_ &&
          e.status.timestamp > 0 &&
          e.status.current_time > 0
      ) {
        const seconds = (Date.now() - e.status.timestamp) / 1000
        currentTime = Math.round((e.status.current_time + seconds) * 100) / 100
      }

      if (currentTime < player.duration()) {
        player.currentTime(currentTime)
      }

      if (typeof playCallback === 'function') {
        playCallback()
        playCallback = null
      } else {
        silencePromise(player.play())
      }
    })
    // 監聽暫停事件
    .listen('PlayerPaused', (e: PlayerPausedEvent) => {
      if (!player) return

      if (import.meta.env.DEV) {
        console.log('PlayerPaused', e.status.current_time)
      }

      player.currentTime(e.status.current_time)

      if (!player.paused()) {
        player.pause()
      }
    })
    // 監聽改變進度條事件 (只發布給其他播放器)
    .listen('PlayerSeeked', (e: PlayerSeekedEvent) => {
      if (!player) return

      if (import.meta.env.DEV) {
        console.log('PlayerSeeked', e.status.current_time)
      }

      const seconds = (Date.now() - e.status.timestamp) / 1000
      player.currentTime(Math.round((e.status.current_time + seconds) * 100) / 100)

      // 如果是[播放]但當前使用者是[暫停]，就要執行[播放]
      if (!e.status.paused && player.paused()) {
        silencePromise(player.play())
      }

      // 如果是[暫停]但當前使用者是[播放]，就要執行[暫停]
      if (e.status.paused && !player.paused()) {
        player.pause()
      }
    })
})

onBeforeUnmount(async () => {
  Echo.leave(`player.${props.type}`)

  if (player) {
    player.pause()
    await promiseTimeout(100)

    player.off('timeupdate', timeUpdate)
    player.off('ended', end)
    await promiseTimeout(100)

    player.dispose()
  }
})
</script>

<style scoped>
:deep(.vjs-youtube.vjs-youtube-block-touch iframe) {
  pointer-events: none !important;
}
</style>
