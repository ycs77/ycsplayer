<template>
  <Field
    :label="label"
    :horizontal="horizontal"
    :error="error || (id ? $page.props.errors[id] : undefined)"
    :tip="tip"
    :class="wrapperClass"
  >
    <div
      v-if="previewImageSrc || defaultImage || placeholder"
      class="mb-3"
      :class="imageWrapperClass"
    >
      <div class="relative">
        <!-- 預覽圖片 -->
        <img
          v-if="previewImageSrc || defaultImage"
          :src="(previewImageSrc || defaultImage) ?? undefined"
          class="object-cover"
          :class="imageClass"
        >

        <!-- 預設佔位灰底區塊 -->
        <ImagePlaceholder v-else :class="imageClass" />

        <!-- 刪除圖片按鈕 -->
        <div v-if="modelFile">
          <button
            type="button"
            class="absolute top-3 right-3 w-6 h-6 flex justify-center items-center bg-gray-700/50 text-white rounded-full"
            :disabled="loading"
            @click="removeSelectedFile"
          >
            <HeroiconsXMark class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <div :class="{ 'text-center': centerButton }">
      <button
        v-if="removeable ? !!defaultImage : false"
        type="button"
        class="btn btn-danger btn-sm"
        :disabled="loading"
        @click="$emit('remove')"
      >
        <HeroiconsTrash class="mr-1" />
        刪除圖片
      </button>

      <button
        v-else
        type="button"
        class="btn btn-primary btn-sm"
        :disabled="loading"
        @click="selectFile"
      >
        <HeroiconsCloudArrowUp class="mr-1" />
        上傳圖片
      </button>
    </div>

    <input
      ref="fileEl"
      type="file"
      accept="image/*"
      class="hidden"
      @change="onChangeFile"
    >
  </Field>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

withDefaults(defineProps<{
  id?: string
  defaultImage?: string | null
  label?: string
  horizontal?: boolean
  error?: string
  tip?: string
  loading?: boolean
  placeholder?: boolean
  removeable?: boolean
  centerButton?: boolean
  wrapperClass?: any
  imageClass?: any
  imageWrapperClass?: any
}>(), {
  type: 'text',
  horizontal: false,
  loading: false,
  placeholder: true,
  imageClass: 'aspect-square',
})

const emit = defineEmits<{
  selected: [file: File, dataUrl: string]
  remove: []
}>()

const modelFile = defineModel<File | null>({ required: true })
const fileEl = ref<HTMLInputElement>(null!)
const previewImageSrc = ref<string | null>(null)

watch(modelFile, modelFile => {
  if (!modelFile) {
    previewImageSrc.value = null
  }
})

function selectFile() {
  fileEl.value.click()
}

function onChangeFile() {
  const files = fileEl.value.files
  const file = files?.[0]

  if (file) {
    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onload = () => {
      const dataUrl = reader.result as string
      previewImageSrc.value = dataUrl
      modelFile.value = file
      nextTick(() => {
        emit('selected', file, dataUrl)
      })
    }
  }
}

function removeSelectedFile() {
  modelFile.value = null
}
</script>
