<template>
  <component
    :is="as"
    v-if="message"
    class="mt-2 text-blue-400 text-xs tracking-wide"
  >
    {{ message }}
  </component>
</template>

<script setup lang="ts">
import type { PropType } from 'vue'

defineProps({
  as: {
    type: [String, Object] as PropType<string | Component>,
    default: 'div',
  },
  message: String,
})
</script>
