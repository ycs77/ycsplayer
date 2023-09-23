<template>
  <div class="absolute top-0 right-0">
    <div v-if="show" class="relative p-1 bg-blue-950/50 text-sm">
      <div class="flex h-[120px] gap-2">
        <div class="w-[200px] flex flex-col">
          <div>Player Status:</div>
          <div class="text-xs overflow-y-auto">
            <div v-for="key in statusKeys" :key="key" class="flex justify-between">
              <div>{{ key }}:</div>
              <div>
                <span v-if="status[key] === true" class="text-green-400">{{ status[key] }}</span>
                <span v-else-if="status[key] === false" class="text-red-400">{{ status[key] }}</span>
                <span v-else-if="status[key] === null" class="text-gray-400">null</span>
                <span v-else>{{ status[key] }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="w-[150px] flex flex-col">
          <div>Logs:</div>
          <div ref="logsRef" class="text-xs overflow-y-auto">
            <div v-for="(log, i) in logs" :key="i" class="text-gray-400">
              <span class="text-white">{{ log.context.mode }}</span> by <span class="text-white">{{ log.context.user }}</span>
            </div>
          </div>
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

interface Status extends Record<string, any> {
  timestamp: number | null
  datetime: number | null
  current_time: number | null
  is_clicked_big_button: boolean | null
  paused: boolean | null
}

interface Log {
  message: string
  context: {
    roomId: string
    mode: string
    user: string
  }
}

const props = defineProps<{
  roomId: string
}>()

const show = ref(true)
const logsRef = ref() as Ref<HTMLDivElement>

const statusKeys = [
  'timestamp',
  'datetime',
  'current_time',
  'is_clicked_big_button',
  'paused',
]

const status = ref({
  timestamp: null,
  current_time: null,
  is_clicked_big_button: null,
  paused: null,
}) as Ref<Status>

const logs = ref([]) as Ref<Log[]>

function fetchDebugStatus() {
  axios.post<{
    status: Status
    logs: Log[]
  }>('/player/debug', {
    room_id: props.roomId,
  }).then(({ data }) => {
    status.value = data.status
    logs.value = data.logs
    nextTick(() => {
      if (logsRef.value) {
        logsRef.value.scrollTo(0, logsRef.value.scrollHeight)
      }
    })
  })
}

fetchDebugStatus()

const timer = setInterval(fetchDebugStatus, 200)

onUnmounted(() => {
  clearInterval(timer)
})
</script>
