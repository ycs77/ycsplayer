/* eslint-disable no-multi-spaces */
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const toast = useToast()

export function Notification() {
  let timer: ReturnType<typeof setTimeout> | undefined

  function notify() {
    // 過濾掉重複呼叫
    if (typeof timer === 'number') return
    timer = setTimeout(() => {
      timer = undefined
    }, 100)

    const page = usePage()

    if (page.props.flash?.success) {
      toast.success(page.props.flash.success)
    }

    if (page.props.flash?.error) {
      toast.error(page.props.flash.error)
    }
  }

  router.on('navigate', notify) // [初次載入] [切換頁面]
  router.on('finish', notify)   //           [切換頁面] [送出表單]
}
