<template>
  <Modal
    v-model="show"
    title="新增播放項目"
    max-width-class="max-w-[560px] w-full"
  >
    <template #icon>
      <HeroiconsPlayCircle class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <MediaTypeSelectField id="type" v-model="form.type" label="媒體類型" />
          <TextInput id="title" v-model="form.title" label="標題" />
          <TextInput
            v-if="form.type === PlayerType.YouTube"
            id="url"
            v-model="form.url"
            label="網址"
          />
          <Field
            v-if="form.type === PlayerType.Video || form.type === PlayerType.Audio"
            label="選擇媒體"
            :error="form.errors.media_id"
          >
            <SelectModel
              v-model="media"
              :models="filteredMediasOptions"
              :disabled="!filteredMediasOptions.length"
              select-message="請選擇已上傳的媒體檔案..."
              thumbnail-width-class="w-28"
              thumbnail-height-class="h-16"
            >
              <template #placeholder>
                <MediaPlaceholder class="w-28 h-16 mr-2 rounded" />
              </template>
            </SelectModel>
          </Field>
        </div>

        <div class="mt-6">
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            新增
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import axios from 'axios'
import type { InertiaForm } from '@inertiajs/vue3'
import { type Media, PlayerType, type PlaylistItemForm } from '@/types'

const props = defineProps<{
  roomId: string
  form: InertiaForm<PlaylistItemForm>
  medias: Media[]
  submitting: boolean
}>()

const emit = defineEmits<{
  submit: [form: InertiaForm<PlaylistItemForm>]
}>()

const show = defineModel<boolean>({ required: true })

const form = reactive(toRaw(props.form)) as InertiaForm<PlaylistItemForm>

const media = ref(null) as Ref<(Media & { title: string }) | null>

const filteredMediasOptions = computed(() => {
  if (form.type === PlayerType.Video) {
    return props.medias
      .filter(media => /\.mp4$/.test(media.name))
      .map(media => ({ ...media, title: media.name }))
  } else if (form.type === PlayerType.Audio) {
    return props.medias
      .filter(media => /\.mp3$/.test(media.name))
      .map(media => ({ ...media, title: media.name }))
  }
  return []
})

watch(() => form.type, () => {
  if (form.type === PlayerType.YouTube) {
    form.url = ''
  } else if (form.type === PlayerType.Video || form.type === PlayerType.Audio) {
    media.value = null
  }
})

watch(media, () => {
  if (media.value) {
    form.media_id = media.value.id
    if ((form.type === PlayerType.Video || form.type === PlayerType.Audio) && !form.title) {
      form.title = media.value.title.replace(/\.\w+$/, '').slice(0, 50)
    }
  } else {
    form.media_id = null
  }
})

watch(() => form.url, () => {
  if (form.type === PlayerType.YouTube && form.url &&
    (
      /^https:\/\/(?:www|music)\.youtube\.com\/watch\?v=/.test(form.url) ||
      /^https:\/\/youtu\.be\//.test(form.url)
    ) && !form.title
  ) {
    axios.post(`/rooms/${props.roomId}/playlist/youtube-title`, {
      url: form.url,
    }).then(({ data }) => {
      if (data && !form.title) {
        form.title = data.slice(0, 50)
      }
    })
  }
})

watch(show, () => {
  if (show.value && !form.media_id && media.value) {
    media.value = null
  }
})

function submit() {
  emit('submit', toRaw(form))
}
</script>
