<template>
  <CardAuth title="重設密碼">
    <form @submit.prevent="submit">
      <div class="space-y-6">
        <TextInput id="password" v-model="form.password" label="新密碼" type="password" />
        <TextInput id="password_confirmation" v-model="form.password_confirmation" label="確認密碼" type="password" />
      </div>

      <div class="mt-6">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          重設
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = defineProps<{
  email: string
  token: string
}>()

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>
