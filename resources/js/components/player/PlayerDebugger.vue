<template>
  <div class="absolute top-0 right-0 font-mono">
    <div v-if="show" class="relative w-[300px] bg-blue-950/75 text-xs sm:w-[360px]">
      <div class="p-1 space-x-1.5">
        <button
          v-for="tab in tabs"
          :key="tab"
          type="button"
          class="underline"
          :class="{
            'bg-blue-900': tab === activeTab,
            'text-gray-300': tab !== activeTab,
          }"
          @click="activeTab = tab"
        >
          {{ tab }}
        </button>
      </div>

      <div
        v-show="activeTab === 'Player status'"
        class="h-[100px] p-1 overflow-y-auto overscroll-y-contain sm:h-[150px]"
      >
        <div v-for="key in statusKeys" :key="key" class="flex justify-between">
          <div>{{ key }}:</div>
          <div>
            <span v-if="status[key] === true" class="text-green-400">{{ status[key] }}</span>
            <span v-else-if="status[key] === false" class="text-red-400">{{ status[key] }}</span>
            <span v-else-if="status[key] === null" class="text-gray-400">null</span>
            <span v-else-if="status[key] === undefined" class="text-gray-400">undefined</span>
            <span v-else class="text-blue-300">{{ status[key] }}</span>
          </div>
        </div>
      </div>

      <div
        v-show="activeTab === 'Client logs'"
        ref="clientLogsRef"
        class="h-[100px] p-1 overflow-y-auto overscroll-y-contain sm:h-[150px]"
      >
        <div v-for="(log, i) in clientLogs" :key="i">
          <span class="text-white">{{ log.message }}: </span>
          <span v-if="log.context === true" class="text-green-400">{{ log.context }}</span>
          <span v-else-if="log.context === false" class="text-red-400">{{ log.context }}</span>
          <span v-else-if="log.context === null" class="text-gray-400">null</span>
          <span v-else-if="log.context === undefined" class="text-gray-400">undefined</span>
          <span v-else class="text-blue-300">{{ log.context }}</span>
        </div>
      </div>

      <div
        v-show="activeTab === 'Server logs'"
        ref="serverLogsRef"
        class="h-[100px] p-1 overflow-y-auto overscroll-y-contain sm:h-[150px]"
      >
        <div v-for="(log, i) in serverLogs" :key="i" class="text-gray-400">
          <span class="text-white">{{ log.context.mode }}</span> by <span class="text-white">{{ log.context.user }}</span>
        </div>
      </div>

      <button type="button" class="absolute top-0 right-0 p-1" @click="show = false">
        [X]
      </button>
    </div>

    <button v-else type="button" class="p-1 bg-blue-950/50 text-sm" @click="show = true">
      debug
    </button>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios'
import type { ServerLog, ServerStatus } from '@/types'

const props = defineProps<{
  roomId: string
}>()

const show = ref(false)

const statusKeys = [
  'timestamp',
  'datetime',
  'current_time',
  'is_clicked_big_button',
  'paused',
]

const tabs = ['Player status', 'Client logs', 'Server logs']
const activeTab = ref(tabs[0])

const clientLogsRef = ref() as Ref<HTMLDivElement>
const serverLogsRef = ref() as Ref<HTMLDivElement>

const status = ref({
  timestamp: null,
  current_time: null,
  is_clicked_big_button: null,
  paused: null,
}) as Ref<ServerStatus>

const serverLogs = ref([]) as Ref<ServerLog[]>

const { logs: clientLogs } = usePlayerLog()!

watch(clientLogs, () => {
  if (clientLogsRef.value) {
    clientLogsRef.value.scrollTo(0, clientLogsRef.value.scrollHeight)
  }
}, { immediate: true, flush: 'post', deep: true })

watch(serverLogs, () => {
  if (serverLogsRef.value) {
    serverLogsRef.value.scrollTo(0, serverLogsRef.value.scrollHeight)
  }
}, { immediate: true, flush: 'post', deep: true })

function fetchDebugStatus() {
  axios.post<{
    status: ServerStatus
    logs: ServerLog[]
  }>('/player/debug', {
    room_id: props.roomId,
  }).then(({ data }) => {
    status.value = data.status
    serverLogs.value = data.logs
  })
}
fetchDebugStatus()

const timer = setInterval(fetchDebugStatus, 200)

onUnmounted(() => {
  clearInterval(timer)
})
</script>
