<template>
  <component
    v-if="value"
    :is="as"
    class="block text-gray-400 text-sm border border-transparent select-none"
  >
    {{ value }}
  </component>
</template>

<script setup lang="ts">
import { PropType } from 'vue'

defineProps({
  as: {
    type: [String, Object] as PropType<string | Component>,
    default: 'label',
  },
  value: String,
})
</script>
