<template>
  <Listbox v-model="selected" as="div" class="relative w-full group">
    <ListboxButton class="
      flex justify-between items-center
      w-full
      px-3 py-2
      bg-blue-950/50
      hover:bg-blue-900/50
      border border-gray-600 rounded
      group-focus-within:border-blue-300 group-focus-within:ring-1 group-focus-within:ring-blue-300
      transition-colors
    ">
      <div class="flex items-center select-none">
        {{ selected?.label ?? selectMessage }}
      </div>
      <SelectButtonIcon chevron-down />
    </ListboxButton>

    <ListboxOptions v-if="options.length" class="
      absolute
      z-[1]
      mt-1.5
      w-full max-h-[200px]
      bg-blue-950
      border border-gray-600 rounded
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
          :class="{ 'bg-blue-900/50': active }"
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
interface Option {
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
