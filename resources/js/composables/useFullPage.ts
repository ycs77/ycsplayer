const showFullPage = ref(false)

interface useFullPageOptions {
  baseClass?: string
  moreClass?: string | string[]
}

export default function useFullPage(show?: boolean, options: useFullPageOptions = {}) {
  const {
    baseClass = 'full-page',
    moreClass,
  } = options

  if (typeof show === 'boolean') {
    showFullPage.value = show

    if (show) {
      const classes = typeof moreClass === 'string' ? [moreClass] : moreClass ?? []

      onMounted(() => {
        if (typeof document !== 'undefined' && showFullPage.value) {
          document.documentElement.classList.add(baseClass, ...classes)
        }
      })

      onBeforeUnmount(() => {
        if (typeof document !== 'undefined') {
          document.documentElement.classList.remove(baseClass, ...classes)
        }
      })
    }
  }

  return showFullPage
}
