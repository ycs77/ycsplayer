<template>
  <video-js
    ref="videoRef"
    class="vjs-theme-forest"
  />
</template>

<script setup lang="ts">
import { debounce } from 'lodash-es'
import { promiseTimeout } from '@vueuse/core'
import videojs from 'video.js'
import videojsZhTW from 'video.js/dist/lang/zh-TW.json'
import type Player from 'video.js/dist/types/player'
import type Component from 'video.js/dist/types/component'
import type VideojsPosterImage from 'video.js/dist/types/poster-image'
import type VideojsBigPlayButton from 'video.js/dist/types/big-play-button'
import type VideojsPlayToggle from 'video.js/dist/types/control-bar/play-toggle'
import type VideojsButton from 'video.js/dist/types/button'
import type VideojsSeekBar from 'video.js/dist/types/control-bar/progress-control/seek-bar'
import 'videojs-youtube'
import { PlayerType } from '@/types'
import type { PlayerPausedEvent, PlayerPlayedEvent, PlayerSeekedEvent, PlayerTimeUpdateEvent } from '@/types'
import 'video.js/dist/video-js.css'
import '@videojs/themes/dist/forest/index.css'
import '@/../css/videojs.css'

const props = withDefaults(defineProps<{
  roomId: string
  src: string
  type: PlayerType
  poster?: string
  operate?: boolean
  forcePlayFromStart?: boolean
}>(), {
  operate: true,
})

const emit = defineEmits<{
  play: [event: PlayerPlayedEvent]
  pause: [event: PlayerPausedEvent]
  seek: [event: PlayerSeekedEvent]
  end: []
  next: []
}>()

declare global {
  interface Window {
    HELP_IMPROVE_VIDEOJS: boolean
  }
}

window.HELP_IMPROVE_VIDEOJS = false

const videoRef = ref() as Ref<HTMLVideoElement>

/**
 * 當開啟播放器，且尚未開始播放時才會更新。
 * 在點擊開始播放的按鈕時，會根據此時間來更新。
 */
const startStatus = {
  otherPlayerIsStarted: false,
  paused: false,
  currentTime: 0,
  timestamp: 0,
}

let player: Player | undefined
let isEnded = false

const { log } = usePlayerLog()

function isClickedBigButton() {
  return player?.hasStarted_ ?? false
}

function paused() {
  return player
    ? player.paused()
    : true
}

function currentTime() {
  return player
    ? Math.round((player.currentTime() || 0) * 100) / 100
    : 0
}

function adjustmentCurrentTime(timestamp: number, currentTime: number) {
  const seconds = (Date.now() - timestamp) / 1000
  return Math.round((currentTime + seconds) * 100) / 100
}

function syncPlayStatusOnStart(callback?: () => void) {
  setTimeout(() => {
    if (!player) return

    if (startStatus.otherPlayerIsStarted) {
      player.currentTime(adjustmentCurrentTime(
        startStatus.timestamp, startStatus.currentTime
      ))

      if (startStatus.paused) {
        setTimeout(() => player!.pause(), 500)
      } else {
        silencePromise(player.play())
      }
    }

    callback?.()
  }, 1)
}

function canStartPlay() {
  return props.operate || startStatus.otherPlayerIsStarted
}

function ready() {
  // 如果是不能播放的使用者 (僅限觀看) 以及其他使用者沒有開始播放，
  // 就不能自動開始。
  if (!canStartPlay()) {
    player?.removeClass('vjs-waiting')
    return
  }

  play(() => {
    setTimeout(() => {
      const playPromise = player?.play()
      if (playPromise) {
        playPromise
          .then(() => {
            if (!player) return

            if (props.forcePlayFromStart) {
              // 有些時候 `play()` 不會成功觸發，需要再呼叫一次。
              silencePromise(player.play())

              emit('play', {
                currentTime: currentTime(),
                timestamp: Date.now(),
              })
            } else if (startStatus.otherPlayerIsStarted) {
              player.currentTime(adjustmentCurrentTime(
                startStatus.timestamp, startStatus.currentTime
              ))

              if (startStatus.paused) {
                setTimeout(() => player!.pause(), 500)
              } else {
                // 有些時候 `play()` 不會成功觸發，需要再呼叫一次。
                silencePromise(player.play())
              }
            } else {
              // 有些時候 `play()` 不會成功觸發，需要再呼叫一次。
              silencePromise(player.play())

              emit('play', {
                currentTime: currentTime(),
                timestamp: Date.now(),
              })
            }
          })
          .catch(() => {
            player?.removeClass('vjs-waiting')
          })
      } else {
        player?.removeClass('vjs-waiting')
      }
    }, 300)
  })
}

function play(handler?: () => void, checkPaused = true) {
  if (!player) return
  if (checkPaused && !player.paused()) return

  handler?.()

  log('[TriggerPlay]')
}

function pause(handler?: () => void) {
  if (!player) return
  if (player.paused()) return

  handler?.()

  log('[TriggerPause]')
}

function seeked() {
  if (!player) return
  if (isEnded) return

  log('[TriggerSeeked]')

  emit('seek', {
    paused: player.paused(),
    currentTime: currentTime(),
    timestamp: Date.now(),
  })
}

function end() {
  if (!player) return
  if (isEnded) return

  isEnded = true

  log('[TriggerEnd]')

  emit('end')
}

onMounted(() => {
  Object.assign(videojsZhTW, {
    Next: '下一項',
  })
  videojs.addLanguage('zh-TW', videojsZhTW)

  const sourceType =
    props.type === PlayerType.YouTube ? 'video/youtube'
      : props.type === PlayerType.Audio ? 'audio/mpeg'
        : 'video/mp4'

  const videojsOptions = {
    controls: true,
    fluid: true,
    sources: [{
      src: props.src,
      type: sourceType,
    }],
  } as Record<string, any>

  if (props.poster && props.type === PlayerType.Video) {
    videojsOptions.poster = props.poster
  }

  if (props.type === PlayerType.YouTube) {
    videojsOptions.techOrder = ['youtube']
    videojsOptions.youtube = {
      hl: navigator.language,
      origin: location.origin,
      ytControls: 0,
      disablekb: 1,
    }
  }

  player = videojs(videoRef.value, videojsOptions)

  // @ts-expect-error
  player.handleTechWaiting_()

  if (!props.operate) {
    player.addClass('vjs-play-only')
  }

  player.ready(() => {
    if (props.type === PlayerType.YouTube)
      ready()
  })

  if (props.type === PlayerType.Video ||
      props.type === PlayerType.Audio
  ) {
    player.on('canplay', () => {
      // @ts-expect-error
      player.handleTechWaiting_()
    })

    player.on('canplaythrough', () => {
      // @ts-expect-error
      player.handleTechWaiting_()

      ready()
    })
  }

  player.on('ended', end)

  class PosterImage extends (videojs.getComponent('PosterImage') as unknown as {
    new (player: Player, options?: any): VideojsPosterImage
  }) {
    handleClick(event: Event) {
      if (!canStartPlay()) return

      if (!this.player_.controls()) {
        return
      }

      if (this.player_.paused()) {
        log('[YcsPosterImage] start play')

        play(() => {
          if (startStatus.otherPlayerIsStarted) {
            this.player_.currentTime(adjustmentCurrentTime(
              startStatus.timestamp, startStatus.currentTime
            ))
          }

          this.player_.play().then(() => {
            syncPlayStatusOnStart(() => {
              if (this.player_.tech(true)) {
                this.player_.tech(true).focus()
              }

              emit('play', {
                currentTime: currentTime(),
                timestamp: Date.now(),
              })
            })
          }, () => {})
        }, false)
      } else {
        this.player_.pause()
      }
    }
  }

  class BigPlayButton extends (videojs.getComponent('BigPlayButton') as unknown as {
    new (player: Player, options?: any): VideojsBigPlayButton
  }) {
    handleClick(event: KeyboardEvent) {
      if (!canStartPlay()) return

      log('[YcsBigPlayButton] start play')

      play(() => {
        if (startStatus.otherPlayerIsStarted) {
          this.player_.currentTime(adjustmentCurrentTime(
            startStatus.timestamp, startStatus.currentTime
          ))
        }

        const playPromise = this.player_.play()

        const playCallback = () => {
          syncPlayStatusOnStart(() => {
            emit('play', {
              currentTime: currentTime(),
              timestamp: Date.now(),
            })
          })
        }

        // exit early if clicked via the mouse
        if (this.mouseused_ && 'clientX' in event && 'clientY' in event) {
          if (isPromise(playPromise)) {
            playPromise.then(playCallback, () => {})
          } else {
            setTimeout(playCallback, 1)
          }
          if (this.player_.tech(true)) {
            this.player_.tech(true).focus()
          }
          return
        }

        const playToggle = this.player_.getChild('controlBar')?.getChild('playToggle')
        if (!playToggle) {
          this.player_.tech(true).focus()
          return
        }

        const playFocus = () => {
          playCallback()

          playToggle.focus()
        }

        if (isPromise(playPromise)) {
          playPromise.then(playFocus, () => {})
        } else {
          setTimeout(playFocus, 1)
        }
      })
    }
  }

  class PlayToggle extends (videojs.getComponent('PlayToggle') as unknown as {
    new (player: Player, options?: any): VideojsPlayToggle
  }) {
    handleClick(event: Event) {
      if (!props.operate) return

      if (this.player_.paused()) {
        log('[YcsPlayToggle] play')

        play(() => {
          this.player_.play().then(() => {
            emit('play', {
              currentTime: currentTime(),
              timestamp: Date.now(),
            })
          }, () => {})
        })
      } else {
        log('[YcsPlayToggle] pause')

        pause(() => {
          this.player_.pause()

          emit('pause', {
            currentTime: currentTime(),
            timestamp: Date.now(),
          })
        })
      }
    }
  }

  class NextButton extends (videojs.getComponent('Button') as unknown as {
    new (player: Player, options?: any): VideojsButton
  }) {
    constructor(player: Player, options?: any) {
      // eslint-disable-next-line constructor-super
      super(player, options)

      this.setIcon('next-item')
      this.controlText('Next')
    }

    buildCSSClass() {
      return `vjs-next-control ${super.buildCSSClass()}`
    }

    handleClick(event: Event) {
      if (!props.operate) return

      emit('next')
    }
  }

  class SeekBar extends (videojs.getComponent('SeekBar') as unknown as {
    new (player: Player, options?: any): VideojsSeekBar
  }) {
    onSekked_: () => void

    constructor(player: Player, options?: any) {
      // eslint-disable-next-line constructor-super
      super(player, options)

      this.onSekked_ = props.operate ? debounce(seeked, 100) : () => {}
    }

    handleMouseUp(event: MouseEvent) {
      if (!props.operate) return

      super.handleMouseUp(event)

      this.onSekked_()
    }
  }

  const posterImage = player.getChild('PosterImage')!
  const bigPlayButton = player.getChild('BigPlayButton')!
  const controlBar = player.getChild('ControlBar')!
  const playToggle = controlBar.getChild('PlayToggle')!
  const remainingTimeDisplay = controlBar.getChild('RemainingTimeDisplay')!
  const pictureInPictureToggle = controlBar.getChild('PictureInPictureToggle')!
  const progressControl = controlBar.getChild('ProgressControl')!
  const seekBar = progressControl.getChild('SeekBar')!
  const playProgressBar = seekBar.getChild('PlayProgressBar')!
  const timeTooltip = playProgressBar.getChild('TimeTooltip')

  player.removeChild(posterImage)
  player.removeChild(bigPlayButton)
  controlBar.removeChild(playToggle)
  controlBar.removeChild(remainingTimeDisplay)
  controlBar.removeChild(pictureInPictureToggle)
  if (timeTooltip) {
    playProgressBar.removeChild(timeTooltip)
  }
  progressControl.removeChild(seekBar)

  videojs.registerComponent('PosterImage', PosterImage as unknown as Component)
  videojs.registerComponent('BigPlayButton', BigPlayButton as unknown as Component)
  videojs.registerComponent('PlayToggle', PlayToggle as unknown as Component)
  videojs.registerComponent('NextButton', NextButton as unknown as Component)
  videojs.registerComponent('SeekBar', SeekBar as unknown as Component)

  player.addChild('PosterImage', {}, 1)
  player.addChild('BigPlayButton', {}, 2)
  controlBar.addChild('PlayToggle', {}, 0)
  controlBar.addChild('NextButton', {}, 1)
  progressControl.addChild('SeekBar', {}, 0)

  player.ready(() => {
    if (!player) return

    const posterImage = player.getChild('posterImage')!
    if (posterImage.hasClass('vjs-hidden')) {
      posterImage.removeClass('vjs-hidden')
    }
  })
})

// 監聽播放事件
function onPlayerPlayed(e: PlayerPlayedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    log('[PlayerPlayed] unclicked start button')
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = false
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = e.timestamp
    if (!props.operate) {
      ready()
    }
    return
  }

  const newCurrentTime = e.currentTime

  log('[PlayerPlayed] currentTime', newCurrentTime)

  if (newCurrentTime < (player.duration() || 0)) {
    player.currentTime(newCurrentTime)
  } else {
    player.currentTime(0)
  }

  silencePromise(player.play())

  isEnded = false
}

// 監聽暫停事件
function onPlayerPaused(e: PlayerPausedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    log('[PlayerPaused] unclicked start button')
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = true
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = e.timestamp
    return
  }

  log('[PlayerPaused] currentTime', e.currentTime)

  player.pause()
}

// 監聽拖曳進度條事件
function onPlayerSeeked(e: PlayerSeekedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    log('[PlayerSeeked] unclicked start button')
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = e.paused
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = e.timestamp
    return
  }

  log('[PlayerSeeked] currentTime', e.currentTime)

  player.currentTime(e.currentTime)

  // 如果是[播放]但當前使用者是[暫停]，就要執行[播放]
  if (!e.paused && player.paused()) {
    silencePromise(player.play())
  }

  // 如果是[暫停]但當前使用者是[播放]，就要執行[暫停]
  if (e.paused && !player.paused()) {
    player.pause()
  }
}

// 監聽更新播放進度事件
function onPlayerTimeUpdate(e: PlayerTimeUpdateEvent) {
  if (!player) return

  log('[PlayerTimeUpdate] currentTime', e.currentTime)

  if (isClickedBigButton()) {
    if (e.currentTime < (player.duration() || 0))
      player.currentTime(e.currentTime)
  } else {
    log('[PlayerTimeUpdate] unclicked start button')
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = e.paused
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = e.timestamp
  }
}

onBeforeUnmount(async () => {
  if (player) {
    player.pause()
    await promiseTimeout(100)

    player.off('ended', end)
    await promiseTimeout(100)

    player.dispose()
  }
})

defineExpose({
  paused,
  currentTime,
  canStartPlay,
  onPlayerPlayed,
  onPlayerPaused,
  onPlayerSeeked,
  onPlayerTimeUpdate,
})
</script>
