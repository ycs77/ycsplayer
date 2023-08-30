<template>
  <Modal
    title="創建房間"
    max-width-class="max-w-[560px] w-full"
    v-model="show"
  >
    <template #icon>
      <HeroiconsPlayCircle class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <form @submit.prevent="form.post('/rooms')">
        <div class="space-y-6">
          <TextInput label="房間名稱" id="name" v-model="form.name" />
          <RoomTypeSelectField id="type" v-model="form.type" />
          <SwitchField label="自動播放" id="auto_play" v-model="form.auto_play" />
          <SwitchField label="播放完畢自動刪除" id="auto_remove" v-model="form.auto_remove" />
        </div>

        <div class="mt-6">
          <button type="submit" class="btn btn-primary" :disabled="form.processing">
            保存
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { RoomType } from '@/types'

const show = defineModel<boolean>({ required: true })

const form = useForm({
  name: '',
  type: RoomType.Video,
  auto_play: false,
  auto_remove: false,
})

watch(() => form.type, () => {
  if (form.type === RoomType.Video) {
    form.auto_play = false
    form.auto_remove = true
  } else if (form.type === RoomType.Audio) {
    form.auto_play = true
    form.auto_remove = false
  }
}, { immediate: true })
</script>
