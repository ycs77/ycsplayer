<template>
  <Badge
    v-if="findRole"
    :color="findRole.color"
    :size="size"
  >
    <slot name="before" />
    {{ findRole.label }}
    <slot name="after" />
  </Badge>
</template>

<script setup lang="ts">
import { roles } from '@/const'

const props = defineProps<{
  role: string
  size?: 'base' | 'lg'
  onlyAdmin?: boolean
}>()

const findRole = ref<typeof roles[number]>()

watch(() => props.role, () => {
  if (props.onlyAdmin) {
    findRole.value = roles.slice(0, 1).find(role => props.role === role.value)
  } else {
    findRole.value = roles.find(role => props.role === role.value)
  }
}, { immediate: true })
</script>
