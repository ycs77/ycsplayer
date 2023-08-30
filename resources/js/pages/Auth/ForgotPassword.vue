<template>
  <CardAuth title="忘記密碼">
    <form @submit.prevent="form.post('/forgot-password')">
      <div class="space-y-6">
        <TextInput label="E-mail" id="email" type="email" v-model="form.email" />
      </div>

      <div
        v-if="status"
        class="mt-4 bg-blue-950/75 px-3 py-2 text-sm text-blue-400 border border-blue-400 rounded-md"
      >
        {{ status }}
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
defineProps<{
  status: string | null
}>()

const form = useForm({
  email: '',
})
</script>
