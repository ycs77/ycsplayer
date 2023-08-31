<template>
  <div class="mx-auto px-[--layout-gap] pb-[--layout-gap] sm:max-w-sm lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <div class="bg-blue-950/50 p-4 rounded-lg lg:p-6">
      <h1 class="text-2xl">{{ title }}</h1>

      <div class="mt-6">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
}>()
</script>
