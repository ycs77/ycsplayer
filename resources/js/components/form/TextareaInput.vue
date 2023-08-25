<template>
  <Field
    :label="label"
    :horizontal="horizontal"
    :error="error || (id ? $page.props.errors[id] : undefined)"
    :tip="tip"
    :class="wrapperClass"
  >
    <textarea
      :id="id"
      :name="id"
      ref="el"
      class="form-textarea"
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      v-bind="$attrs"
    ></textarea>
  </Field>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = withDefaults(defineProps<{
  modelValue?: string
  id?: string
  label?: string
  horizontal?: boolean
  error?: string
  tip?: string
  wrapperClass?: any
  autofocus?: boolean
}>(), {
  horizontal: false,
})

defineEmits<{
  (e: 'update:modelValue', value?: string): void
}>()

const el = ref<HTMLElement>(null!)

onMounted(() => {
  if (props.autofocus) el.value.focus()
})
</script>
