<template>
  <div v-if="content" class="bg-blue-950/75 px-3 py-2 text-sm text-blue-400 border border-blue-400 rounded-md">
    {{ content }}
  </div>
</template>

<script setup lang="ts">
defineProps<{
  content?: string | null
}>()
</script>
