<template>
  <div class="max-w-sm mx-auto px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="bg-blue-950/50 p-4 rounded-lg lg:p-6">
      <h1 class="text-2xl">E-mail 驗證</h1>

      <div class="mt-6">
        <form @submit.prevent="submit">
          <div>
            請先開啟您的信箱點擊 E-mail 驗證連結。
          </div>

          <div
            v-if="verificationLinkSent"
            class="mt-4 bg-blue-950/75 px-3 py-2 text-sm text-blue-400 border border-blue-400 rounded-md"
          >
            新的驗證連結已經重新發送了，請到您的信箱確認信件。
          </div>

          <div class="mt-6">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
              重新發送 E-mail
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  status: string | null
}>()

const form = useForm({})

const verificationLinkSent = computed(() => props.status === 'verification-link-sent')

function submit() {
  form.post('/email/verification-notification')
}
</script>
