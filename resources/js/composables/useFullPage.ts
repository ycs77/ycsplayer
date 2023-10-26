const showFullPage = ref(false)
const pageCanScroll = ref(true)

interface useFullPageOptions {
  baseClass?: string
  moreClass?: string | string[]
  scroll?: boolean
}

export default function useFullPage(show?: boolean, options: useFullPageOptions = {}) {
  const {
    baseClass = 'full-page',
    moreClass,
    scroll = false,
  } = options

  if (typeof show === 'boolean') {
    showFullPage.value = show
    pageCanScroll.value = scroll

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

  return { showFullPage, pageCanScroll }
}
