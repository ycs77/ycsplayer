<template>
  <Field
    :label="label"
    :error="error ?? fileError"
    :tip="tip"
    :class="wrapperClass"
  >
    <div v-if="progress" class="mt-4">
      <div class="mb-2 text-center">上傳中... </div>
      <div class="h-2 bg-blue-900/50 rounded-full overflow-hidden">
        <div class="bg-blue-500/50 h-full transition-[width] duration-500" :style="{ width: `${progressPer}%` }" />
      </div>
    </div>

    <button
      v-else
      ref="browseFilesBtnRef"
      type="button"
      class="btn btn-primary"
    >
      <HeroiconsCloudArrowUp class="mr-1" />上傳檔案
    </button>
  </Field>
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
  success: []
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
    simultaneousUploads: 1,
    permanentErrors: [400, 404, 409, 415, 422, 500, 501], // add 422
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

  resumable.on('fileSuccess', (file: any, res: string) => {
    setTimeout(() => {
      progress.value = false

      emit('success')
    }, 500)
  })

  resumable.on('fileError', (file, response) => {
    progress.value = false

    if (response.startsWith('{"') && response.endsWith('}')) {
      const data = JSON.parse(response)
      fileError.value = data.message
    } else {
      console.error(response)
    }
  })
})
</script>
