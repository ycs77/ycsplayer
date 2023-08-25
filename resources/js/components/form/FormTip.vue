<template>
  <component
    v-if="message"
    :is="as"
    class="mt-2 text-blue-400 text-xs tracking-wide"
  >
    {{ message }}
  </component>
</template>

<script setup lang="ts">
import { PropType } from 'vue'

defineProps({
  as: {
    type: [String, Object] as PropType<string | Component>,
    default: 'div',
  },
  message: String,
})
</script>
