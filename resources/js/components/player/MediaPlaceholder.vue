<template>
  <div class="flex justify-center items-center bg-blue-900/50 rounded-lg aspect-video">
    <HeroiconsPlayCircle class="text-blue-800/50" :class="iconSizeClass" />
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  iconSizeClass?: string
}>(), {
  iconSizeClass: 'w-12 h-12',
})
</script>
