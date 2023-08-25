<template>
  <div :class="{ 'flex': horizontal }">
    <FormLabel
      :value="label"
      :class="horizontal ? 'w-[70px] py-1' : 'mb-2'"
    />

    <div :class="{ 'flex-1': horizontal }">
      <slot></slot>
    </div>

    <FormError :message="error" />
    <FormTip :message="tip" />
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  label?: string
  horizontal?: boolean
  error?: string
  tip?: string
}>(), {
  horizontal: false,
})
</script>
