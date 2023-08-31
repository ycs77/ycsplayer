<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <Card title="帳號設定">
      <form @submit.prevent="submit">
        <div class="space-y-6">
          <TextInput id="name" v-model="form.name" label="姓名" />
          <TextInput id="email" v-model="form.email" label="E-mail" type="email" />
          <template v-if="!passwordLess">
            <TextInput id="current_password" v-model="form.current_password" label="舊密碼" type="password" />
            <TextInput id="password" v-model="form.password" label="新密碼" type="password" />
            <TextInput id="password_confirmation" v-model="form.password_confirmation" label="確認密碼" type="password" />
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
