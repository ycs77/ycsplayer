<template>
  <CardAuth title="忘記密碼">
    <form @submit.prevent="form.post('/forgot-password')">
      <Message v-if="status" :content="status" class="mb-4" />

      <div class="space-y-6">
        <TextInput id="email" v-model="form.email" label="E-mail" type="email" />
      </div>

      <div class="mt-6">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
          重設密碼
        </button>
      </div>
    </form>
  </CardAuth>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

defineProps<{
  status: string | null
}>()

const form = useForm({
  email: '',
})
</script>
