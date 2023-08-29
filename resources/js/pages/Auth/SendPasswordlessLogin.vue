<template>
  <div class="max-w-sm mx-auto px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="bg-blue-950/50 p-4 rounded-lg lg:p-6">
      <h1 class="text-2xl">登入</h1>

      <div class="mt-6">
        <form @submit.prevent="submit">
          <div>
            請點擊下方按鈕發送登入 E-mail 至 <span class="text-blue-400 break-words">{{ email }}</span>，並開啟您的信箱點擊登入連結。
          </div>

          <div class="mt-6">
            <button v-if="!waiting" type="submit" class="btn btn-primary">
              發送登入 E-mail
            </button>
            <button v-else type="submit" class="btn btn-primary" disabled>
              等候 {{ seconds }} 秒可重新發送
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  email: string
  seconds: number | null
}>()

const seconds = ref(0)
const waiting = computed(() => seconds.value > 0)

function submit() {
  router.post('/login/send', {
    email: props.email,
  })
}

watch(() => props.seconds, () => {
  if (typeof props.seconds === 'number') {
    seconds.value = props.seconds
    const timer = setInterval(() => {
      seconds.value--
      if (seconds.value <= 0) {
        clearInterval(timer)
      }
    }, 1000)
  }
}, { immediate: true })
</script>
