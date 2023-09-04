<template>
  <Listbox v-model="selected" as="div" class="relative w-full group">
    <ListboxButton
      class="
        flex justify-between items-center
        w-full
        pl-3 pr-9 py-2
        bg-blue-950/50
        hover:bg-blue-900/50
        border border-gray-600 rounded
        group-focus-within:border-blue-300 group-focus-within:ring-1 group-focus-within:ring-blue-300
        transition-colors
      "
    >
      <slot name="option" :option="selected" :label="selected?.label ?? selectMessage">
        <span class="truncate select-none">
          {{ selected?.label ?? selectMessage }}
        </span>
      </slot>
      <SelectButtonIcon chevron-down />
    </ListboxButton>

    <ListboxOptions
      v-if="options.length"
      class="
        absolute
        z-[1]
        mt-1.5
        w-full max-h-[200px]
        bg-blue-950
        border border-gray-600 rounded
        overflow-y-auto
        shadow-lg
        focus:outline-none
      "
    >
      <ListboxOption
        v-for="option in options"
        :key="option.value"
        v-slot="{ selected, active }"
        :value="option"
        as="template"
      >
        <li
          class="relative flex items-center pl-3 pr-9 py-2 border border-transparent transition-colors cursor-pointer"
          :class="{ 'bg-blue-900/50 text-white': active }"
        >
          <slot name="option" :option="option" :label="option.label">
            <span class="truncate select-none">
              {{ option.label }}
            </span>
          </slot>
          <SelectSelectedIcon v-if="selected" :active="active" />
        </li>
      </ListboxOption>
    </ListboxOptions>
  </Listbox>
</template>

<script setup lang="ts">
interface Option extends Record<string, any> {
  label: string
  value: any
}

withDefaults(defineProps<{
  options: Option[]
  selectMessage?: string
}>(), {
  selectMessage: '請選擇...',
})

const selected = defineModel<Option | null>({ required: true })
</script>
