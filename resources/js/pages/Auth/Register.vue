<template>
  <div class="max-w-sm mx-auto px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="bg-blue-950/50 p-4 rounded-lg lg:p-6">
      <h1 class="text-2xl">註冊</h1>

      <div class="mt-6">
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
            <button type="submit" class="btn btn-primary">
              註冊
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
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
