<template>
  <Field :error="error">
    <SwitchGroup as="div" class="flex items-center">
      <div class="flex-1">
        <FormLabel :as="SwitchLabel" :value="label" />
        <FormTip :as="SwitchDescription" :message="tip" class="!mt-1" />
      </div>
      <Switch v-model="enabled" />
    </SwitchGroup>
  </Field>
</template>

<script setup lang="ts">
import { SwitchLabel, SwitchDescription } from '@headlessui/vue'

const props = withDefaults(defineProps<{
  modelValue: boolean
  label?: string
  error?: string
  tip?: string
}>(), {
  modelValue: false,
})

defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const enabled = useVModel(props, 'modelValue')
</script>
