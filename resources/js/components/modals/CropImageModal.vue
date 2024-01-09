<template>
  <Modal
    v-model="show"
    :title="title"
    max-width-class="max-w-[560px] w-full"
  >
    <template #icon>
      <HeroiconsPhoto class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <div class="cropper-circle">
        <img ref="imgRef" :src="src ?? ''">
      </div>

      <div class="mt-6 flex justify-end">
        <button type="button" class="btn btn-primary" :disabled="loading" @click="cropped">
          確定
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { promiseTimeout } from '@vueuse/core'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const props = withDefaults(defineProps<{
  title?: string
  src: string | undefined
  aspectRatio: number
  circle?: boolean
  mimeType?: string
  loading?: boolean
  options?: Cropper.Options<HTMLImageElement>
}>(), {
  title: '剪裁圖片',
  circle: false,
  mimeType: 'auto',
  loading: false,
})

const emit = defineEmits<{
  cropped: [blob: Blob]
}>()

const show = defineModel<boolean>({ required: true })

let cropper: Cropper | undefined

const imgRef = ref<HTMLImageElement | null>(null)
const mimeType = ref(props.mimeType)

async function cropped() {
  if (!cropper) return

  const blob = await new Promise<Blob | null>(resolve => {
    cropper!
      .getCroppedCanvas()
      .toBlob(blob => {
        resolve(blob)
      }, mimeType.value)
  })

  show.value = false

  if (blob) {
    emit('cropped', blob)
  }
}

watch(show, async (show, oldShow, invalidate) => {
  await promiseTimeout(10)
  if (props.src && imgRef.value) {
    cropper = new Cropper(imgRef.value, {
      aspectRatio: props.aspectRatio,
      viewMode: 1,
      ...(props.options ?? {}),
    })

    invalidate(() => {
      cropper?.destroy()
    })
  }
}, { flush: 'post' })

watch(() => props.src, src => {
  if (!src) return

  const mimetypeMap = {
    jpg: 'image/jpeg',
    jpeg: 'image/jpeg',
    png: 'image/png',
    gif: 'image/gif',
    svg: 'image/svg+xml',
    webp: 'image/webp',
  } as Record<string, string>

  try {
    // check the src is base64 or not (throw exception)
    atob(src.split(',')[1])

    // checked is base64
    mimeType.value = src.split(':')[1].split(';')[0]
  } catch (error) {
    if (error instanceof DOMException) {
      // is image
      const ext = src.match(/\.(\w+)$/)?.[1]
      if (ext) {
        mimeType.value = mimetypeMap[ext]
      }
    }
  }

  // If guess is not work, return the default mime-type
  if (mimeType.value === 'auto' ||
    !(mimeType.value in Object.keys(mimetypeMap))
  ) {
    mimeType.value = 'image/jpeg'
  }
})
</script>

<style scoped>
.cropper-circle :deep(.cropper-view-box),
.cropper-circle :deep(.cropper-face) {
  border-radius: 50%;
}

/* The css styles for `outline` do not follow `border-radius` on iOS/Safari (cropperjs#979). */
.cropper-circle :deep(.cropper-view-box) {
  outline: 0;
  box-shadow: 0 0 0 1px #39f;
}
</style>
