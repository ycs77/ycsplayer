<template>
  <CardAuth title="重設密碼">
    <form @submit.prevent="submit">
      <div class="space-y-6">
        <TextInput label="新密碼" id="password" type="password" v-model="form.password" />
        <TextInput label="確認密碼" id="password_confirmation" type="password" v-model="form.password_confirmation" />
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
const props = defineProps<{
  email: string,
  token: string,
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
