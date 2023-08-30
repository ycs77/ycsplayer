<template>
  <div class="max-w-screen-md mx-auto bg-blue-950/50 p-4 rounded-lg lg:p-6">
    <slot name="title">
      <h1 v-if="title" class="text-2xl mb-6">
        {{ title }}
      </h1>
    </slot>

    <div>
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
}>()
</script>
