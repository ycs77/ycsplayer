<template>
  <Listbox v-model="selected" as="div" class="relative w-full group">
    <ListboxButton class="
      flex justify-between items-center
      w-full
      px-3 py-2
      hover:bg-gray-50
      border border-gray-200 rounded
      group-focus-within:border-blue-500 group-focus-within:ring-1 group-focus-within:ring-blue-500
      transition-colors
    ">
      <div v-if="selected" class="flex items-center select-none">
        {{ selected.label }}
      </div>
      <SelectButtonIcon chevron-down />
    </ListboxButton>

    <ListboxOptions v-if="options.length" class="
      absolute
      z-[1]
      mt-1.5
      w-full max-h-[200px]
      bg-white
      border border-gray-200 rounded
      overflow-y-auto
      shadow-lg
      focus:outline-none
    ">
      <ListboxOption
        v-for="option in options"
        :key="option.value"
        :value="option"
        v-slot="{ selected, active }"
        as="template"
      >
        <li
          class="relative flex items-center px-3 py-2 border border-transparent transition-colors cursor-pointer"
          :class="{ 'bg-blue-500': active }"
        >
          <span class="truncate select-none" :class="{ 'text-white': active }">
            {{ option.label }}
          </span>
          <SelectSelectedIcon v-if="selected" :active="active" />
        </li>
      </ListboxOption>
    </ListboxOptions>
  </Listbox>
</template>

<script setup lang="ts">
import type { Ref } from 'vue'

interface Option {
  label: string
  value: any
}

const props = defineProps<{
  modelValue: Option | null
  options: Option[]
}>()

const selected = useVModel(props) as Ref<Option>
</script>
