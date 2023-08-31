<template>
  <CardAuth title="確認密碼">
    <form @submit.prevent="form.post('/user/confirm-password')">
      <div class="space-y-6">
        <TextInput id="password" v-model="form.password" label="密碼" type="password" />
      </div>

      <div class="mt-6 flex justify-between items-center">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          確認
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
const form = useForm({
  password: '',
})
</script>
