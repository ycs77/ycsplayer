<template>
  <CardAuth title="E-mail 驗證">
    <form @submit.prevent="submit">
      <div>
        請先開啟您的信箱點擊 E-mail 驗證連結。
      </div>

      <Message
        v-if="verificationLinkSent"
        content="新的驗證連結已經重新發送了，請到您的信箱確認信件。"
        class="mt-4"
      />

      <div class="mt-6">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          重新發送 E-mail
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
const props = defineProps<{
  status: string | null
}>()

defineOptions({ inheritAttrs: false })

const form = useForm({})

const verificationLinkSent = computed(() => props.status === 'verification-link-sent')

function submit() {
  form.post('/email/verification-notification')
}
</script>
