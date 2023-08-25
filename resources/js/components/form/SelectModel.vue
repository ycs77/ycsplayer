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
        <img v-if="selected.thumbnail" :src="selected.thumbnail" class="mr-2 w-10 h-10 rounded" />
        <ImagePlaceholder v-else class="mr-2 w-10 h-10 rounded" />
        {{ selected.title }}
      </div>
      <SelectButtonIcon chevron-down />
    </ListboxButton>

    <ListboxOptions v-if="models.length" class="
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
        v-for="model in models"
        :key="model.id"
        :value="model"
        v-slot="{ selected, active }"
        as="template"
      >
        <li
          class="relative flex items-center px-3 py-2 border border-transparent transition-colors cursor-pointer select-none"
          :class="{ 'bg-blue-500': active }"
        >
          <img v-if="model.thumbnail" :src="model.thumbnail" class="mr-2 w-10 h-10 rounded" />
          <ImagePlaceholder v-else class="mr-2 w-10 h-10 rounded" />
          <span class="truncate" :class="{ 'text-white': active }">
            {{ model.title }}
          </span>
          <SelectSelectedIcon v-if="selected" :active="active" />
        </li>
      </ListboxOption>
    </ListboxOptions>
  </Listbox>
</template>

<script setup lang="ts">
import type { Ref } from 'vue'

export interface Model extends Record<string, any> {
  id: string
  title: string
  thumbnail: string | null
}

const props = defineProps<{
  modelValue: Model | null
  models: Model[]
}>()

const selected = useVModel(props) as Ref<Model>
</script>
