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
      ref="el"
      v-model="modelValue"
      :name="id"
      class="form-textarea"
      v-bind="$attrs"
    />
  </Field>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

const props = withDefaults(defineProps<{
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

const modelValue = defineModel<string>()

const el = ref<HTMLElement>(null!)

onMounted(() => {
  if (props.autofocus) el.value.focus()
})
</script>
