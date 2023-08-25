<template>
  <component
    :is="is"
    class="inline-flex justify-center items-center h-8 gap-x-0.5 text-white text-sm rounded-full transition-colors whitespace-nowrap lg:h-10"
    :class="{
      'px-2 lg:px-3': onlyIcon,
      'px-3.5 lg:px-4': !onlyIcon,
      'bg-blue-800/50 hover:bg-blue-700/50': active,
      'bg-blue-950/50 hover:bg-blue-900/50': !active,
    }"
  >
    <slot name="icon" /><slot />
  </component>
</template>

<script setup lang="ts">
const attrs = useAttrs()

defineProps<{
  onlyIcon?: boolean
  active?: boolean
}>()

const is = computed(() => {
  if (attrs.href) return 'Link'
  return 'button'
})
</script>
