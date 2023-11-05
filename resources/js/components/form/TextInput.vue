<template>
  <Field
    :label="label"
    :horizontal="horizontal"
    :error="error || (id ? $page.props.errors[id] : undefined)"
    :tip="tip"
    :class="wrapperClass"
  >
    <input
      :id="id"
      ref="el"
      v-model="modelValue"
      :type="type"
      :name="id"
      class="form-input"
      v-bind="$attrs"
    >
  </Field>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = withDefaults(defineProps<{
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

const modelValue = defineModel<string | number>()

const el = ref<HTMLElement>(null!)

onMounted(() => {
  if (props.autofocus) el.value.focus()
})
</script>
