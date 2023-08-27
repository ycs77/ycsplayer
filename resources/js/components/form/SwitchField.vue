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

defineProps<{
  label?: string
  error?: string
  tip?: string
}>()

const enabled = defineModel<boolean>({ required: true })
</script>
