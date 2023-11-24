<template>
  <Field :error="error || (id ? $page.props.errors[id] : undefined)">
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
import { SwitchDescription, SwitchLabel } from '@headlessui/vue'

defineProps<{
  id?: string
  label?: string
  error?: string
  tip?: string
}>()

const enabled = defineModel<boolean>({ required: true })
</script>
