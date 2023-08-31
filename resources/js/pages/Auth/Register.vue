<template>
  <CardAuth title="註冊">
    <form @submit.prevent="form.post('/register')">
      <div class="space-y-6">
        <TextInput id="name" v-model="form.name" label="姓名" />
        <TextInput id="email" v-model="form.email" label="E-mail" type="email" />
        <template v-if="!passwordLess">
          <TextInput id="password" v-model="form.password" label="密碼" type="password" />
          <TextInput id="password_confirmation" v-model="form.password_confirmation" label="確認密碼" type="password" />
        </template>
      </div>

      <div class="mt-6">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          註冊
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
defineProps<{
  passwordLess: boolean
}>()

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
</script>
