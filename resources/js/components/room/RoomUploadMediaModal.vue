<template>
  <Modal
    v-model="show"
    title="上傳檔案"
    max-width-class="max-w-[560px] w-full"
  >
    <template #icon>
      <HeroiconsCloudArrowUp class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <FileUpload
        :key="fileInputKey"
        label="選擇檔案"
        :target="`/rooms/${roomId}/upload`"
        :file-type="['mp4', 'mp3']"
        :csrf-token="csrfToken"
        :error="fileError"
        @start="uploadStart"
        @success="uploaded"
        @error="uploadFail"
      />
    </div>
  </Modal>
</template>

<script setup lang="ts">
defineProps<{
  roomId: string
  csrfToken: string
}>()

const emit = defineEmits<{
  uploaded: [message: string | null]
}>()

const show = defineModel<boolean>({ required: true })

const fileInputKey = ref(Date.now())
const fileError = ref<string | undefined>(undefined)

function uploadStart() {
  fileError.value = undefined
}

function uploaded(message: string | null) {
  show.value = false
  emit('uploaded', message)
}

function uploadFail(message: string) {
  updateKey()
  fileError.value = message
}

function updateKey() {
  fileInputKey.value = Date.now()
}

watch(show, show => {
  if (show) {
    updateKey()
  }
})
</script>
