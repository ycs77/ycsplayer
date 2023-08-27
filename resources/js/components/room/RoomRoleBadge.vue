<template>
  <Badge v-if="role === 'admin'" color="blue">管理員</Badge>
  <template v-else-if="!onlyAdmin">
    <Badge v-if="role === 'user'" color="green">成員</Badge>
  </template>
</template>

<script setup lang="ts">
defineProps<{
  role: string
  onlyAdmin?: boolean
}>()
</script>
