<template>
  <Modal
    title="新增播放項目"
    max-width-class="max-w-[560px] w-full"
    v-model="show"
  >
    <template #icon>
      <HeroiconsPlayCircle class="mr-1" />
    </template>

    <div class="mt-4 relative">
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <MediaTypeSelectField label="媒體類型" id="type" v-model="form.type" />
          <TextInput label="標題" id="title" v-model="form.title" />
          <TextInput
            v-if="form.type === PlayerType.YouTube"
            label="網址"
            id="url"
            v-model="form.url"
          />
          <Field
            v-if="form.type === PlayerType.Video || form.type === PlayerType.Audio"
            label="選擇媒體"
            :error="form.errors.media_id"
          >
            <SelectModel
              v-model="media"
              :models="filteredMedias.map(media => ({ ...media, title: media.name }))"
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
          <button type="submit" class="btn btn-primary">
            新增
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3'
import { PlayerType, type Media, type PlaylistItemForm } from '@/types'

const props = defineProps<{
  form: InertiaForm<PlaylistItemForm>
  medias: Media[]
}>()

const emit = defineEmits<{
  'submit': [form: InertiaForm<PlaylistItemForm>]
}>()

const show = defineModel<boolean>({ required: true })

const form = reactive(toRaw(props.form)) as InertiaForm<PlaylistItemForm>

const media = ref(null) as Ref<(Media & { title: string }) | null>

const filteredMedias = computed(() => {
  if (form.type === PlayerType.Video) {
    return props.medias.filter(media => /\.mp4$/.test(media.name))
  } else if (form.type === PlayerType.Audio) {
    return props.medias.filter(media => /\.mp3$/.test(media.name))
  } else {
    return []
  }
})

watch(() => form.type, () => {
  if (form.type === PlayerType.Video || form.type === PlayerType.Audio) {
    media.value = null
  } else if (form.type === PlayerType.YouTube) {
    form.url = ''
  }
})

watch(media, () => {
  if (media.value) {
    form.media_id = media.value.id
    if ((form.type === PlayerType.Video || form.type === PlayerType.Audio) && ! form.title) {
      form.title = media.value.title.replace(/\.\w+$/, '')
    }
  } else {
    form.media_id = null
  }
})

watch(show, () => {
  if (show.value && ! form.media_id && media.value) {
    media.value = null
  }
})

function submit() {
  emit('submit', toRaw(form))
}
</script>
