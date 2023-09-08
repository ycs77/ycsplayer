export function useWaitingCountdown(seconds: Ref<number> | (() => number)) {
  const currentSeconds = ref(0)
  const waiting = computed(() => currentSeconds.value > 0)

  watch(seconds, seconds => {
    if (typeof seconds === 'number') {
      currentSeconds.value = seconds
      const timer = setInterval(() => {
        currentSeconds.value--
        if (currentSeconds.value <= 0) {
          clearInterval(timer)
        }
      }, 1000)
    }
  }, { immediate: true })

  return { currentSeconds, waiting }
}
