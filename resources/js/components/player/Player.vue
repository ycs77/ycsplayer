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
  waitOtherPlayers?: boolean
}>(), {
  operate: true,
  forcePlayFromStart: false,
  waitOtherPlayers: false,
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

const playerCreated = ref(false)
const playerEnded = ref(false)
const playerReady = ref(false)

const {
  timestampFetched,
  offsetTimestamp,
  toServerTimestamp,
  toClientTimestamp,
} = usePlayerServerTimestamp(props.roomId)

const { canAutoPlay, isDetected: canAutoPlayIsDetected } = useCanAutoPlay()

// props.waitOtherPlayers 為 true 時 (當觸發 `onOtherPlayerTimeUpdate()`)，
// 是因為有其他播放器有連線，需要等待其他播放器連線成功後才能播放。
//
// waitedOtherPlayers 則是當完成確認其他播放器的狀態。
//
// 如果當前播放器第一個上線，沒有其他播放器，因此：
// - props.waitOtherPlayers 為 false
// - waitedOtherPlayers 為 true
//
// 如果當前播放器上線時已經有其他播放器，則會：
// - props.waitOtherPlayers 為 true
// - waitedOtherPlayers 為 false
// - 需要等觸發 `onOtherPlayerTimeUpdate()` 後
//   waitedOtherPlayers 轉為 true
const waitedOtherPlayers = ref(!props.waitOtherPlayers)

// 等待其他播放器連線最多 5 秒後就會自動開始播放
let waitedOtherPlayersTimer: ReturnType<typeof setTimeout> | undefined
waitedOtherPlayersTimer = setTimeout(() => {
  waitedOtherPlayers.value = true
}, 5000)
function finishWaitingOtherPlayers() {
  waitedOtherPlayers.value = true
  clearTimeout(waitedOtherPlayersTimer)
  waitedOtherPlayersTimer = undefined
}

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
  const now = Date.now()
  const seconds = (now - timestamp) / 1000
  const newCurrentTime = Math.round((currentTime + seconds) * 100) / 100
  log('[adjustmentCurrentTime]', { now, timestamp, seconds, currentTime, newCurrentTime })
  return newCurrentTime
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

  /**
   * 有些時候 `play()` 不會成功觸發，需要再呼叫一次。
   */
  function reTriggerPlay(resolve?: () => void) {
    if (!player) return

    wrapPromise(player.play()).then(() => {
      log('[StartPlay] retrigger play successfully')

      if (IS_MOBILE) {
        player?.removeClass('vjs-waiting')
        setTimeout(() => {
          if (player?.paused())
            player?.removeClass('vjs-waiting')
        }, 500)
      }

      resolve?.()
    }).catch(() => {
      log('[StartPlay] retrigger play error')

      player?.removeClass('vjs-waiting')
    })
  }

  play(() => {
    setTimeout(() => {
      wrapPromise(player?.play())
        .then(() => {
          if (!player) return

          log('[StartPlay] play successfully')

          if (props.forcePlayFromStart) {
            log('[StartPlay] force play from start')

            reTriggerPlay()
          } else if (startStatus.otherPlayerIsStarted) {
            const newCurrentTime = adjustmentCurrentTime(
              startStatus.timestamp, startStatus.currentTime
            )

            log('[StartPlay] other player is started', {
              timestamp: startStatus.timestamp,
              currentTime: startStatus.currentTime,
              adjustment: newCurrentTime,
            })

            player.currentTime(newCurrentTime)

            if (startStatus.paused) {
              setTimeout(() => {
                player!.pause()
                player!.removeClass('vjs-waiting')
              }, 500)
            } else {
              reTriggerPlay()
            }
          } else {
            log('[StartPlay] normal start play')

            reTriggerPlay(() => {
              emit('play', {
                currentTime: currentTime(),
                timestamp: toServerTimestamp(Date.now()),
              })
            })
          }
        })
        .catch(() => {
          log('[StartPlay] play error')

          player?.removeClass('vjs-waiting')
        })
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
  if (!isClickedBigButton()) return
  if (playerEnded.value) return

  const status = {
    paused: player.paused(),
    currentTime: currentTime(),
    timestamp: toServerTimestamp(Date.now()),
  } satisfies PlayerSeekedEvent

  log('[TriggerSeeked]', status)

  emit('seek', status)
}

function end() {
  if (!player) return
  if (playerEnded.value) return

  playerEnded.value = true

  log('[TriggerEnd]')

  emit('end')
}

// 自動播放
watch([
  playerReady,
  canAutoPlayIsDetected,
  timestampFetched,
  waitedOtherPlayers,
], () => {
  if (!player) return
  if (!(
    playerReady.value &&
    canAutoPlayIsDetected.value &&
    timestampFetched.value &&
    waitedOtherPlayers.value
  )) return

  log('[ServerTimestamp] offset ms', offsetTimestamp.value)

  if (!canAutoPlay.value) {
    log('[AutoPlay]', false)

    player?.removeClass('vjs-waiting')
    setTimeout(() => {
      player?.removeClass('vjs-waiting')
    }, 500)

    return
  }

  log('[AutoPlay]', true)

  ready()
})

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
    playsinline: true,
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

  playerCreated.value = true

  // @ts-ignore
  player.handleTechWaiting_()

  if (!props.operate) {
    player.addClass('vjs-play-only')
  }

  if (props.type === PlayerType.YouTube) {
    player.ready(() => {
      playerReady.value = true
    })
  }

  if (props.type === PlayerType.Video || props.type === PlayerType.Audio) {
    if (IS_iOS) {
      player.on('loadedmetadata', () => {
        // @ts-ignore
        player.handleTechWaiting_()

        playerReady.value = true
      })
    } else {
      player.on('canplay', () => {
        // @ts-ignore
        player.handleTechWaiting_()
      })

      player.on('canplaythrough', () => {
        // @ts-ignore
        player.handleTechWaiting_()

        playerReady.value = true
      })
    }
  }

  player.on('ended', end)

  class PosterImage extends (videojs.getComponent('PosterImage') as unknown as {
    new (player: Player, options?: any): VideojsPosterImage
  }) {
    handleClick(_event: Event) {
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

              if (!startStatus.otherPlayerIsStarted) {
                emit('play', {
                  currentTime: currentTime(),
                  timestamp: toServerTimestamp(Date.now()),
                })
              }
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
            if (!startStatus.otherPlayerIsStarted) {
              emit('play', {
                currentTime: currentTime(),
                timestamp: toServerTimestamp(Date.now()),
              })
            }
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
    handleClick(_event: Event) {
      if (!props.operate) return

      if (this.player_.paused()) {
        log('[YcsPlayToggle] play')

        play(() => {
          this.player_.play().then(() => {
            const status = {
              currentTime: currentTime(),
              timestamp: toServerTimestamp(Date.now()),
            } satisfies PlayerPlayedEvent

            log('[YcsPlayToggle] play status', status)

            emit('play', status)
          }, () => {})
        })
      } else {
        log('[YcsPlayToggle] pause')

        pause(() => {
          this.player_.pause()

          emit('pause', {
            currentTime: currentTime(),
            timestamp: toServerTimestamp(Date.now()),
          })
        })
      }
    }
  }

  class NextButton extends (videojs.getComponent('Button') as unknown as {
    new (player: Player, options?: any): VideojsButton
  }) {
    constructor(player: Player, options?: any) {
      super(player, options)

      this.setIcon('next-item')
      this.controlText('Next')
    }

    buildCSSClass() {
      return `vjs-next-control ${super.buildCSSClass()}`
    }

    handleClick(_event: Event) {
      if (!props.operate) return

      emit('next')
    }
  }

  class SeekBar extends (videojs.getComponent('SeekBar') as unknown as {
    new (player: Player, options?: any): VideojsSeekBar
  }) {
    onSekked_: () => void

    constructor(player: Player, options?: any) {
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
function onOtherPlayerPlayed(e: PlayerPlayedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    log('[PlayerPlayed] unclicked start button')
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = false
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = toClientTimestamp(e.timestamp)
    if (!props.operate) {
      ready()
    }
    return
  }

  const newCurrentTime = e.currentTime

  log('[PlayerPlayed] currentTime', newCurrentTime)

  if (newCurrentTime < (player.duration() || 0)) {
    player.currentTime(newCurrentTime)
  }

  silencePromise(player.play())

  playerEnded.value = false
}

// 監聽暫停事件
function onOtherPlayerPaused(e: PlayerPausedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = true
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = toClientTimestamp(e.timestamp)
    log('[PlayerPaused] unclicked start button', startStatus)
    return
  }

  log('[PlayerPaused] currentTime', e.currentTime)

  player.pause()
}

// 監聽拖曳進度條事件
function onOtherPlayerSeeked(e: PlayerSeekedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    startStatus.otherPlayerIsStarted = true
    startStatus.paused = e.paused
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = toClientTimestamp(e.timestamp)
    log('[PlayerSeeked] unclicked start button', startStatus)
    return
  }

  const newCurrentTime = e.currentTime

  log('[PlayerSeeked] currentTime', newCurrentTime)

  player.currentTime(newCurrentTime)

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
function onOtherPlayerTimeUpdate(e: PlayerTimeUpdateEvent) {
  if (!player) return

  log('[PlayerTimeUpdate] currentTime', e.currentTime)

  if (isClickedBigButton()) {
    if (e.currentTime < (player.duration() || 0))
      player.currentTime(e.currentTime)
  } else {
    finishWaitingOtherPlayers()

    startStatus.otherPlayerIsStarted = true
    startStatus.paused = e.paused
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = toClientTimestamp(e.timestamp)
    log('[PlayerTimeUpdate] unclicked start button', startStatus)
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
  isClickedBigButton,
  toServerTimestamp,
  toClientTimestamp,
  onOtherPlayerPlayed,
  onOtherPlayerPaused,
  onOtherPlayerSeeked,
  onOtherPlayerTimeUpdate,
})
</script>
