<template>
  <CardAuth title="登入">
    <form @submit.prevent="form.post('/login')">
      <div class="space-y-6">
        <TextInput label="E-mail" id="email" type="email" v-model="form.email" />
        <TextInput v-if="!passwordLess" label="密碼" id="password" type="password" v-model="form.password" />
      </div>

      <div class="mt-6">
        <button type="submit" class="btn btn-primary">
          {{ passwordLess ? '下一步' : '登入' }}
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
const props = defineProps<{
  passwordLess: boolean
}>()

const form = useForm({
  email: '',
  password: props.passwordLess ? '__skip' : '',
})
</script>
