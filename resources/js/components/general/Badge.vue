<template>
  <span class="inline-block px-2.5 py-0.5 rounded-full text-white tracking-wide text-xs" :class="colorClass">
    <slot />
  </span>
</template>

<script setup lang="ts">
const props = defineProps<{
  color: 'red' | 'green' | 'blue'
}>()

const colorClass = computed(() => {
  if (props.color === 'red') {
    return 'bg-red-600'
  } else if (props.color === 'green') {
    return 'bg-green-600'
  } else if (props.color === 'blue') {
    return 'bg-blue-600'
  }
})
</script>
