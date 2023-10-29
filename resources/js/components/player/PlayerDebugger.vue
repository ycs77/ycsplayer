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
        v-show="activeTab === 'Time'"
        class="h-[100px] p-1 overflow-y-auto overscroll-y-contain sm:h-[150px]"
      >
        <div>Now date: {{ date }}</div>
        <div>Timestamp: {{ timestamp }}</div>
        <div class="mt-1">
          <button
            v-if="isNowUpdating"
            type="button"
            class="btn btn-primary px-3 py-1 text-xs"
            @click="stopUpdateNow"
          >
            暫停
          </button>
          <button
            v-else
            type="button"
            class="btn btn-primary px-3 py-1 text-xs"
            @click="startUpdateNow"
          >
            繼續
          </button>
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
const show = ref(false)

const tabs = ['Client logs', 'Time']
const activeTab = ref(tabs[0])

const clientLogsRef = ref() as Ref<HTMLDivElement>

const { logs: clientLogs } = usePlayerLog()!

const date = ref(new Date().toLocaleString())
const timestamp = ref(Date.now())
const isNowUpdating = ref(true)
let updateNowTimer: ReturnType<typeof setInterval> | undefined

function updateNow() {
  date.value = new Date().toLocaleString()
  timestamp.value = Date.now()
}
function startUpdateNow() {
  updateNowTimer = setInterval(updateNow, 1)
  isNowUpdating.value = true
}
function stopUpdateNow() {
  clearInterval(updateNowTimer)
  updateNowTimer = undefined
  isNowUpdating.value = false
}

watch(show, (show, _, onCleanup) => {
  if (show) {
    nextTick(() => {
      scrollToBottom(clientLogsRef)
    })

    startUpdateNow()
    onCleanup(() => {
      stopUpdateNow()
    })
  }
})

watch(clientLogs, () => {
  scrollToBottom(clientLogsRef)
}, { immediate: true, flush: 'post', deep: true })

function scrollToBottom(el: Ref<HTMLElement>) {
  if (el.value) {
    el.value.scrollTo(0, el.value.scrollHeight)
  }
}
</script>
