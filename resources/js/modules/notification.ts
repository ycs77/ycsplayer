import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const toast = useToast()

export function Notification() {
  router.on('finish', e => {
    const page = usePage()

    if (page.props.flash.success) {
      toast.success(page.props.flash.success)
    }

    if (page.props.flash.error) {

    }
  })
}
