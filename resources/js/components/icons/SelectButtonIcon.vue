<template>
  <span
    class="absolute inset-y-0 right-0 flex items-center pointer-events-none"
    :class="paddingClass"
  >
    <slot>
      <HeroiconsChevronDown v-if="chevronDown" class="h-5 w-5 text-gray-400" />
      <HeroiconsChevronUpDown v-else class="h-5 w-5 text-gray-400" />
    </slot>
  </span>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  chevronDown?: boolean
  paddingClass?: string
}>(), {
  paddingClass: 'pr-3',
})
</script>
