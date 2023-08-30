<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <Card title="帳號設定">
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <TextInput label="姓名" id="name" v-model="form.name" />
          <TextInput label="E-mail" id="email" type="email" v-model="form.email" />
          <template v-if="!passwordLess">
            <TextInput label="舊密碼" id="current_password" type="password" v-model="form.current_password" />
            <TextInput label="新密碼" id="password" type="password" v-model="form.password" />
            <TextInput label="確認密碼" id="password_confirmation" type="password" v-model="form.password_confirmation" />
          </template>
        </div>

        <div class="mt-6">
          <button type="submit" class="btn btn-primary" :disabled="form.processing">
            保存
          </button>
        </div>
      </form>
    </Card>

    <Card title="刪除帳號" class="mt-8">
      <button type="button" class="btn btn-danger" @click="deleteAccount">
        刪除帳號
      </button>
    </Card>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  passwordLess: boolean
}>()

const { user } = useAuth()

const form = useForm({
  name: user.value?.name,
  email: user.value?.email,
  current_password: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.put('/user/profile-information', {
    onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
    onError: () => form.reset('current_password', 'password', 'password_confirmation'),
  })
}

function deleteAccount() {
  if (confirm('確定要刪除此帳號嗎? 此操作將無法恢復')) {
    router.get('/user/destroy/confirm')
  }
}
</script>
