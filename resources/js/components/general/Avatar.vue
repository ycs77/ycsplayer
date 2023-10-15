<template>
  <img
    class="rounded-full"
    :src="src ?? userPlaceholderSrc"
  >
</template>

<script setup lang="ts">
import userPlaceholderSrc from '@/images/user.svg'

defineProps<{
  src?: string | null
}>()
</script>
