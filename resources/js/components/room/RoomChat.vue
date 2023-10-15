<template>
  <div class="flex flex-col gap-4 bg-blue-950/50 p-4 rounded-lg">
    <ul ref="messagesContainer" class="grow min-h-0 space-y-2 overflow-y-auto">
      <li v-for="message in messages" :key="message.timestamp">
        <div class="flex gap-2">
          <Avatar class="w-9 h-9 mt-1" :src="message.user.avatar" />
          <div class="grow min-w-0">
            <div class="text-sm truncate">{{ message.user.name }}</div>
            <div class="text-gray-400 break-all">{{ message.content }}</div>
          </div>
        </div>
      </li>
    </ul>

    <form @submit.prevent="sendMessage">
      <div class="flex items-center gap-2">
        <input ref="inputEl" v-model="input" type="text" class="form-input grow min-w-0" maxlength="1000">
        <button type="submit" class="shrink-0 btn btn-primary w-9 h-9 p-0 rounded-md">
          <HeroiconsPaperAirplane class="w-5 h-5" />
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { RoomChatMessage } from '@/types'

const props = defineProps<{
  messages: RoomChatMessage[]
}>()

const emit = defineEmits<{
  sendMessage: [message: RoomChatMessage]
  markAllRead: []
}>()

const { user } = useAuth()

const messagesContainer = ref(null) as Ref<HTMLUListElement | null>
const inputEl = ref(null) as Ref<HTMLInputElement | null>

const input = ref('')

function sendMessage() {
  if (!input.value) return

  const message = {
    user: {
      name: user.value!.name,
      avatar: user.value!.avatar,
    },
    content: input.value,
    timestamp: Date.now(),
  } satisfies RoomChatMessage

  input.value = ''

  emit('sendMessage', message)
}

watch(() => props.messages, () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTo(0, messagesContainer.value.scrollHeight)
  }

  emit('markAllRead')
}, { immediate: true, deep: true, flush: 'post' })

onMounted(() => {
  if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
    inputEl.value?.focus()
  }
})
</script>
