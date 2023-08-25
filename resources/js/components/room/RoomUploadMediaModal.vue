<template>
  <Modal
    title="上傳檔案"
    max-width-class="max-w-[560px] w-full"
    v-model="show"
  >
    <template #icon>
      <HeroiconsCloudArrowUp class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <FileUpload
        label="選擇檔案"
        :target="`/rooms/${roomId}/upload`"
        :csrf-token="csrfToken"
        :file-type="['mp4', 'mp3']"
        @success="uploaded"
      />
    </div>
  </Modal>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: boolean
  roomId: number
  csrfToken: string
}>()

const emit = defineEmits<{
  'update:modelValue': [show: boolean]
  'uploaded': []
}>()

const show = useVModel(props)

function uploaded() {
  show.value = false
  emit('uploaded')
}
</script>
