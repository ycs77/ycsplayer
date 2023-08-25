<template>
  <Switch
    v-model="enabled"
    :class="enabled ? 'bg-blue-500' : 'bg-gray-300'"
    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
  >
    <span
      aria-hidden="true"
      :class="enabled ? 'translate-x-5' : 'translate-x-0'"
      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition duration-200 ease-in-out"
    />
  </Switch>
</template>

<script setup lang="ts">
import { Switch } from '@headlessui/vue'

const props = withDefaults(defineProps<{
  modelValue: boolean
}>(), {
  modelValue: false,
})

defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const enabled = useVModel(props)
</script>
