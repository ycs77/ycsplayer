import canAutoPlayDetector from 'can-autoplay'

export function useCanAutoPlay() {
  const canAutoPlay = ref(false)
  const isDetected = ref(false)

  onMounted(() => {
    canAutoPlayDetector
      .video({ timeout: 200, inline: true })
      .then(({ result }) => {
        canAutoPlay.value = result
        isDetected.value = true
      })
  })

  return { canAutoPlay, isDetected }
}
