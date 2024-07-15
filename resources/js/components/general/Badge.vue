<template>
  <span
    class="inline-flex justify-center items-center rounded-full text-white tracking-wide"
    :class="[colorClass, sizeClass]"
  >
    <slot />
  </span>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  color: 'red' | 'yellow' | 'green' | 'blue'
  size?: 'base' | 'lg'
}>(), {
  size: 'base',
})

const colorClass = computed(() => {
  if (props.color === 'red') {
    return 'bg-red-600'
  } else if (props.color === 'yellow') {
    return 'bg-yellow-600'
  } else if (props.color === 'green') {
    return 'bg-green-600'
  } else if (props.color === 'blue') {
    return 'bg-blue-600'
  }
  return ''
})

const sizeClass = computed(() => {
  if (props.size === 'base') {
    return 'px-2.5 py-0.5 text-xs'
  } else if (props.size === 'lg') {
    return 'px-3.5 py-0.5 text-sm'
  }
  return ''
})
</script>
