<template>
  <video-js
    ref="videoRef"
    class="vjs-theme-forest"
  />
</template>

<script setup lang="ts">
import { debounce } from 'lodash-es'
import { promiseTimeout } from '@vueuse/core'
import 'video.js/dist/video-js.css'
import '@videojs/themes/dist/forest/index.css'
import videojs from 'video.js'
import videojsZhTW from 'video.js/dist/lang/zh-TW.json'
import type Player from 'video.js/dist/types/player'
import type Component from 'video.js/dist/types/component'
import type VideojsPosterImage from 'video.js/dist/types/poster-image'
import type VideojsBigPlayButton from 'video.js/dist/types/big-play-button'
import type VideojsPlayToggle from 'video.js/dist/types/control-bar/play-toggle'
import type VideojsSeekBar from 'video.js/dist/types/control-bar/progress-control/seek-bar'
import 'videojs-youtube'
import { PlayerType } from '@/types'
import type { PlayerPausedEvent, PlayerPlayedEvent, PlayerSeekedEvent, PlayerTimeUpdateEvent } from '@/types'

const props = defineProps<{
  roomId: string
  src: string
  type: PlayerType
  poster?: string
  autoplay?: boolean
}>()

const emit = defineEmits<{
  play: [event: PlayerPlayedEvent]
  pause: [event: PlayerPausedEvent]
  seek: [event: PlayerSeekedEvent]
  end: []
}>()

const videoRef = ref() as Ref<HTMLVideoElement>
const startStatus = {
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

function syncPlayStatus(callback?: () => void) {
  setTimeout(() => {
    if (!player) return

    if (startStatus.currentTime > 0) {
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
      disablekb: 1,
    }
  }

  player = videojs(videoRef.value, videojsOptions)

  player.on('ended', end)

  player.ready(() => {
    if (props.autoplay) {
      play(() => {
        setTimeout(() => {
          player?.play()?.then(() => {
            if (!player) return

            if (startStatus.currentTime > 0) {
              player.currentTime(adjustmentCurrentTime(
                startStatus.timestamp, startStatus.currentTime
              ))

              if (startStatus.paused) {
                setTimeout(() => player!.pause(), 500)
              } else {
                silencePromise(player.play())
              }
            } else {
              emit('play', {
                currentTime: currentTime(),
                timestamp: Date.now(),
              })
            }
          }, () => {})
        }, 300)
      })
    }
  })

  class PosterImage extends (videojs.getComponent('PosterImage') as unknown as {
    new (player: Player, options?: any): VideojsPosterImage
  }) {
    handleClick(event: Event) {
      if (!this.player_.controls()) {
        return
      }

      if (this.player_.paused()) {
        log('[YcsPosterImage] start play')

        play(() => {
          if (startStatus.currentTime > 0) {
            this.player_.currentTime(adjustmentCurrentTime(
              startStatus.timestamp, startStatus.currentTime
            ))
          }

          this.player_.play().then(() => {
            syncPlayStatus(() => {
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
      log('[YcsBigPlayButton] start play')

      play(() => {
        if (startStatus.currentTime > 0) {
          this.player_.currentTime(adjustmentCurrentTime(
            startStatus.timestamp, startStatus.currentTime
          ))
        }

        const playPromise = this.player_.play()

        const playCallback = () => {
          syncPlayStatus(() => {
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

  class YcsSeekBar extends (videojs.getComponent('SeekBar') as unknown as {
    new (player: Player, options?: any): SeekBar
  }) {
    onSekked_: () => void

    constructor(player: Player, options?: any) {
      // eslint-disable-next-line constructor-super
      super(player, options)

      this.onSekked_ = debounce(seeked, 100)
    }

    handleMouseUp(event: MouseEvent) {
      super.handleMouseUp(event)

      this.onSekked_()
    }
  }

  const posterImage = player.getChild('PosterImage')!
  const bigPlayButton = player.getChild('BigPlayButton')!
  const controlBar = player.getChild('ControlBar')!
  const playToggle = controlBar.getChild('PlayToggle')!
  const durationDisplay = controlBar.getChild('DurationDisplay')!
  const remainingTimeDisplay = controlBar.getChild('RemainingTimeDisplay')!
  const pictureInPictureToggle = controlBar.getChild('PictureInPictureToggle')!
  const progressControl = controlBar.getChild('ProgressControl')!
  const seekBar = progressControl.getChild('SeekBar')!
  const playProgressBar = seekBar.getChild('PlayProgressBar')!
  const timeTooltip = playProgressBar.getChild('TimeTooltip')

  player.removeChild(posterImage)
  player.removeChild(bigPlayButton)
  controlBar.removeChild(playToggle)
  controlBar.removeChild(durationDisplay)
  controlBar.removeChild(remainingTimeDisplay)
  controlBar.removeChild(pictureInPictureToggle)
  if (timeTooltip) {
    playProgressBar.removeChild(timeTooltip)
  }
  progressControl.removeChild(seekBar)

  videojs.registerComponent('PosterImage', PosterImage as unknown as Component)
  videojs.registerComponent('BigPlayButton', BigPlayButton as unknown as Component)
  videojs.registerComponent('PlayToggle', PlayToggle as unknown as Component)
  videojs.registerComponent('SeekBar', SeekBar as unknown as Component)

  player.addChild('PosterImage', {}, 1)
  player.addChild('BigPlayButton', {}, 2)
  controlBar.addChild('PlayToggle', {}, 0)
  controlBar.addChild('DurationDisplay', {}, 7)
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
    startStatus.paused = false
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = e.timestamp
    return
  }

  const newCurrentTime = adjustmentCurrentTime(e.timestamp, e.currentTime)

  log('[PlayerPlayed] currentTime', newCurrentTime)

  if (newCurrentTime < (player.duration() || 0)) {
    player.currentTime(newCurrentTime)
  }

  silencePromise(player.play())

  isEnded = false
}

// 監聽暫停事件
function onPlayerPaused(e: PlayerPausedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
    startStatus.paused = true
    startStatus.currentTime = e.currentTime
    startStatus.timestamp = e.timestamp
    return
  }

  log('[PlayerPaused] currentTime', e.currentTime)

  player.currentTime(e.currentTime)

  player.pause()
}

// 監聽拖曳進度條事件
function onPlayerSeeked(e: PlayerSeekedEvent) {
  if (!player) return
  if (!isClickedBigButton()) {
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
  onPlayerPlayed,
  onPlayerPaused,
  onPlayerSeeked,
  onPlayerTimeUpdate,
})
</script>

<style>
.video-js .vjs-duration {
  display: block !important;
}

.vjs-control-bar::before {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: -1em;
  display: block;
  height: 80px;
  background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAADGCAYAAAAT+OqFAAAAdklEQVQoz42QQQ7AIAgEF/T/D+kbq/RWAlnQyyazA4aoAB4FsBSA/bFjuF1EOL7VbrIrBuusmrt4ZZORfb6ehbWdnRHEIiITaEUKa5EJqUakRSaEYBJSCY2dEstQY7AuxahwXFrvZmWl2rh4JZ07z9dLtesfNj5q0FU3A5ObbwAAAABJRU5ErkJggg==') bottom / auto 200% repeat-x;
  pointer-events: none;
}

.vjs-youtube iframe {
  pointer-events: none !important;
}
</style>
