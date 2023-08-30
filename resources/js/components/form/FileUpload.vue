<template>
  <div v-if="!progress" class="my-6">
    <Field
      :label="label"
      :error="error ?? fileError"
      :tip="tip"
      :class="wrapperClass"
    >
      <button
        ref="browseFilesBtnRef"
        type="button"
        class="btn btn-primary"
      >
        <HeroiconsCloudArrowUp class="mr-1" />上傳檔案
      </button>
    </Field>
  </div>

  <div v-else class="my-16">
    <div class="text-center">上傳中... </div>
    <div class="mt-4 h-2 bg-blue-900/50 rounded-full overflow-hidden">
      <div class="bg-blue-500/50 h-full transition-[width] duration-500" :style="{ width: `${progressPer}%` }" />
    </div>
  </div>
</template>

<script setup lang="ts">
import Resumable from 'resumablejs'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  target: string
  csrfToken: string
  fileType: string[]
  label?: string
  error?: string
  tip?: string
  wrapperClass?: any
}>()

const emit = defineEmits<{
  success: [message: string]
}>()

const browseFilesBtnRef = ref(null!) as Ref<HTMLInputElement>
const progress = ref(false)
const progressPer = ref(0)
const fileError = ref<string | undefined>(undefined)

onMounted(() => {
  const resumable = new Resumable({
    target: props.target,
    query: { _token: props.csrfToken },
    fileType: props.fileType,
    headers: {
      'Accept': 'application/json',
    },
    chunkSize: 5 * 1024 * 1024, // 5M
    testChunks: false,
    simultaneousUploads: 1, // 同時上傳數量只能設 1，設 2 以上 PHP 會發生錯誤。
    permanentErrors: [400, 404, 409, 415, 422, 500, 501], // 增加 422 Code，當驗證錯誤時不需要報錯誤訊息。
  })

  resumable.assignBrowse(browseFilesBtnRef.value, false)

  resumable.on('fileAdded', file => {
    fileError.value = undefined

    progress.value = true
    progressPer.value = 0

    resumable.upload()
  })

  resumable.on('fileProgress', file => {
    progressPer.value = Math.floor(file.progress(false) * 100)
  })

  resumable.on('fileSuccess', (file: any, response: string) => {
    setTimeout(() => {
      progress.value = false

      const data = JSON.parse(response) as { success: string }

      emit('success', data.success)
    }, 500)
  })

  resumable.on('fileError', (file, response) => {
    progress.value = false

    try {
      const data = JSON.parse(response)
      fileError.value = data.message
    } catch (e) {
      console.error(response)
    }
  })
})
</script>
