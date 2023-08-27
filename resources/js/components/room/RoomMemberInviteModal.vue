<template>
  <Modal
    title="邀請房間成員"
    max-width-class="max-w-[560px] w-full"
    v-model="show"
  >
    <template #icon>
      <HeroiconsUser class="mr-1" />
    </template>

    <div class="mt-8 relative">
      <div>
        <h5 class="text-lg">邀請連結</h5>
        <div v-if="!url" class="mt-4">
          <button type="submit" class="btn btn-primary" @click="generateLink">
            <HeroiconsLink class="w-4 h-4 mr-1" />產生邀請連結
          </button>
        </div>
        <div v-if="url" class="mt-4 relative">
          <TextInput
            ref="inputRef"
            :model-value="url"
            class="font-mono"
            readonly
            @focus="selectUrl"
          />

          <button
            type="button"
            class="absolute top-2 right-2 p-1 flex items-center bg-blue-950/50 text-blue-400 text-xs rounded ring-1 ring-blue-400 backdrop-blur-sm sm:top-1.5"
            @click="selectUrl"
          >
            <template v-if="copied">
              <HeroiconsClipboardDocumentCheck class="mr-1" />複製成功
            </template>
            <template v-else class="flex items-center">
              <HeroiconsClipboard class="mr-1" />點選複製
            </template>
          </button>
        </div>
      </div>

      <div class="mt-8">
        <div class="text-blue-600 text-sm mb-4">或者...</div>

        <form @submit.prevent="inviteMember">
          <h5 class="text-lg">直接輸入 E-mail 邀請加入</h5>
          <div class="mt-4">
            <TextInput label="E-mail" id="email" v-model="form.email" autocomplete="off" />
          </div>
          <div v-if="member" class="mt-4 flex items-center">
            <img
              class="w-8 h-8 rounded-full mr-2"
              :src="member.avatar ?? '/images/user.svg'"
            />
            <div class="flex items-center">
              <div class="tracking-wide">{{ member.name }}</div>
            </div>
          </div>
          <div class="mt-6">
            <button type="submit" class="btn btn-primary" :disabled="form.processing || !member">
              <HeroiconsPlus class="w-4 h-4 mr-1" />加入房間
            </button>
          </div>
        </form>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import axios from 'axios'
import { debounce } from 'lodash-es'
import type { RoomMember } from '@/types'

const props = defineProps<{
  roomId: string
}>()

const emit = defineEmits<{
  invite: []
}>()

const show = defineModel<boolean>({ required: true })
const inputRef = ref() as Ref<{ $el: HTMLElement }>

const form = useForm({
  email: '',
})

const member = ref(null) as Ref<RoomMember | null>

const url = ref('')

const { copy, copied } = useClipboard({ source: url })

function generateLink() {
  axios.post(`/rooms/${props.roomId}/generate-join-link`).then(({ data }) => {
    url.value = data.join_link
  })
}

function selectUrl() {
  const el = inputRef.value.$el.querySelector('input')
  if (el) {
    el.select()
    copy()
  }
}

function searchMember() {
  axios.post(`/rooms/${props.roomId}/search-member`, {
    email: form.email,
  }).then(({ data }) => {
    member.value = data.member
  })
}

function inviteMember() {
  form.post(`/rooms/${props.roomId}/invite`, {
    only: ['errors', 'members'],
    preserveScroll: true,
    onSuccess() {
      emit('invite')
    },
  })
}

watch(() => form.email, debounce(searchMember, 500))
</script>