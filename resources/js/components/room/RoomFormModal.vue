<template>
  <Modal
    v-model="show"
    title="創建房間"
    max-width-class="max-w-[560px] w-full"
  >
    <template #icon>
      <HeroiconsPlayCircle class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <form @submit.prevent="form.post('/rooms')">
        <div class="space-y-6">
          <TextInput id="name" v-model="form.name" label="房間名稱" />
          <RoomTypeSelectField id="type" v-model="form.type" />
          <SwitchField id="auto_play" v-model="form.auto_play" label="連續播放" />
          <SwitchField id="auto_remove" v-model="form.auto_remove" label="播放完畢自動刪除" />
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
