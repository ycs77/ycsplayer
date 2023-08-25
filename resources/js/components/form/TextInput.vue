<template>
  <Field
    :label="label"
    :horizontal="horizontal"
    :error="error || (id ? $page.props.errors[id] : undefined)"
    :tip="tip"
    :class="wrapperClass"
  >
    <input
      :type="type"
      :id="id"
      :name="id"
      ref="el"
      class="form-input"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      v-bind="$attrs"
    />
  </Field>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = withDefaults(defineProps<{
  modelValue?: string | number
  id?: string
  type?: string
  label?: string
  horizontal?: boolean
  error?: string
  tip?: string
  wrapperClass?: any
  autofocus?: boolean
}>(), {
  type: 'text',
  horizontal: false,
})

defineEmits<{
  (e: 'update:modelValue', value?: string | number): void
}>()

const el = ref<HTMLElement>(null!)

onMounted(() => {
  if (props.autofocus) el.value.focus()
})
</script>
