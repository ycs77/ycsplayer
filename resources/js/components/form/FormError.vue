<template>
  <div v-if="message" class="mt-1 block text-sm text-red-500">{{ message }}</div>
</template>

<script setup lang="ts">
defineProps<{ message?: string }>()
</script>
