<template>
  <CardAuth title="註冊">
    <form @submit.prevent="form.post('/register')">
      <div class="space-y-6">
        <TextInput label="姓名" id="name" v-model="form.name" />
        <TextInput label="E-mail" id="email" type="email" v-model="form.email" />
        <template v-if="!passwordLess">
          <TextInput label="密碼" id="password" type="password" v-model="form.password" />
          <TextInput label="確認密碼" id="password_confirmation" type="password" v-model="form.password_confirmation" />
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
