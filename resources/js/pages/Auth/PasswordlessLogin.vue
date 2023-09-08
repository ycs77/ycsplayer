<template>
  <CardAuth title="登入">
    <form @submit.prevent="submit">
      <div>
        請點擊下方按鈕發送登入 E-mail 至 <span class="text-blue-400 break-words">{{ email }}</span>，並開啟您的信箱點擊登入連結。
      </div>

      <div class="mt-6">
        <button v-if="!waiting" type="submit" class="btn btn-primary" :disabled="form.processing">
          發送登入 E-mail
        </button>
        <button v-else type="submit" class="btn btn-primary" disabled>
          等候 {{ currentSeconds }} 秒可重新發送
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
const props = defineProps<{
  email: string
  seconds: number
}>()

const form = useForm({
  email: props.email,
})

const { currentSeconds, waiting } = useWaitingCountdown(() => props.seconds)

function submit() {
  form.post('/login/send', {
    preserveState: false,
  })
}
</script>
