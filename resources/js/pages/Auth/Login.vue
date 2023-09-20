<template>
  <CardAuth title="登入">
    <form @submit.prevent="form.post('/login')">
      <Message v-if="status" :content="status" class="mb-4" />

      <div class="space-y-6">
        <TextInput id="email" v-model="form.email" label="E-mail" type="email" />
        <TextInput v-if="!passwordLess" id="password" v-model="form.password" label="密碼" type="password" />
      </div>

      <div class="mt-6 flex justify-between items-center">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          {{ passwordLess ? '下一步' : '登入' }}
        </button>

        <Link v-if="!passwordLess && mail" href="/forgot-password" class="underline">
          忘記密碼?
        </Link>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
const props = defineProps<{
  mail: boolean
  passwordLess: boolean
  status: string | null
}>()

const form = useForm({
  email: '',
  password: props.passwordLess ? '__skip__' : '',
  remember: true,
})
</script>
