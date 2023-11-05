<template>
  <div v-if="!progress">
    <Field
      :label="label"
      :error="error"
      :tip="tip"
      :class="wrapperClass"
    >
      <button
        ref="browseFilesBtnRef"
        type="button"
        :class="buttonClass"
      >
        <HeroiconsCloudArrowUp class="mr-1" />上傳檔案
      </button>
    </Field>
  </div>

  <div v-else>
    <slot name="progressing" :progress-per="progressPer">
      <div class="text-center">上傳中... </div>
      <div class="mt-4 h-2 bg-blue-900/50 rounded-full overflow-hidden">
        <div
          class="bg-blue-500/50 h-full transition-[width] duration-500"
          :style="{ width: `${progressPer}%` }"
        />
      </div>
    </slot>
  </div>
</template>

<script setup lang="ts">
import Resumable from 'resumablejs'

defineOptions({ inheritAttrs: false })

const props = withDefaults(defineProps<{
  target: string
  csrfToken: string
  fileType: string[]
  label?: string
  error?: string
  tip?: string
  buttonClass?: any
  wrapperClass?: any
}>(), {
  buttonClass: 'btn btn-primary',
})

const emit = defineEmits<{
  start: []
  success: [message: string | null]
  error: [message: string]
}>()

const browseFilesBtnRef = ref(null!) as Ref<HTMLInputElement>
const progress = ref(false)
const progressPer = ref(0)

onMounted(() => {
  const resumable = new Resumable({
    target: props.target,
    query: { _token: props.csrfToken },
    fileType: props.fileType,
    headers: {
      Accept: 'application/json',
    },
    chunkSize: 5 * 1024 * 1024, // 5M
    testChunks: false,
    simultaneousUploads: 1, // 同時上傳數量只能設 1，設 2 以上 PHP 會發生錯誤。
    permanentErrors: [400, 404, 409, 415, 422, 500, 501], // 增加 422 Code，當驗證錯誤時不需要報錯誤訊息。
  })

  resumable.assignBrowse(browseFilesBtnRef.value, false)

  resumable.on('fileAdded', _file => {
    progress.value = true
    progressPer.value = 0

    emit('start')

    resumable.upload()
  })

  resumable.on('fileProgress', file => {
    progressPer.value = Math.floor(file.progress(false) * 100)
  })

  resumable.on('fileSuccess', (file: any, response: string) => {
    setTimeout(() => {
      progress.value = false

      const data = JSON.parse(response) as { success: string | null }

      emit('success', data.success)
    }, 500)
  })

  resumable.on('fileError', (file, response) => {
    progress.value = false

    try {
      const data = JSON.parse(response)
      emit('error', data.message)
    } catch (e) {
      console.error(response)
    }
  })
})
</script>
