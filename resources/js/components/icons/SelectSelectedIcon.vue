<template>
  <span
    class="absolute inset-y-0 right-0 flex items-center"
    :class="[active ? 'text-white' : 'text-sky-500', paddingClass]"
  >
    <HeroiconsCheck class="h-5 w-5" />
  </span>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  active?: boolean
  paddingClass?: string
}>(), {
  paddingClass: 'pr-3',
})
</script>
