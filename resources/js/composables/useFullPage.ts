const showFullPage = ref(false)

export default function useFullPage(show?: boolean, moreClass?: string | string[]) {
  if (typeof show === 'boolean') {
    showFullPage.value = show

    if (show) {
      const moreClasses = typeof moreClass === 'string' ? [moreClass] : moreClass ?? []

      onMounted(() => {
        if (typeof document !== 'undefined' && showFullPage.value) {
          document.documentElement.classList.add('full-page', ...moreClasses)
        }
      })

      onBeforeUnmount(() => {
        if (typeof document !== 'undefined') {
          document.documentElement.classList.remove('full-page', ...moreClasses)
        }
      })
    }
  }

  return showFullPage
}
