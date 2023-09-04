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
      <div class="flex items-center select-none">
        <img
          v-if="selected?.thumbnail"
          :src="selected.thumbnail"
          class="mr-2 rounded object-cover"
          :class="[thumbnailWidthClass, thumbnailHeightClass]"
        >
        <slot v-else name="placeholder">
          <ImagePlaceholder
            class="mr-2 rounded"
            :class="[thumbnailWidthClass, thumbnailHeightClass]"
          />
        </slot>
        {{ selected?.title ?? selectMessage }}
      </div>
      <SelectButtonIcon chevron-down />
    </ListboxButton>

    <ListboxOptions
      v-if="models.length"
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
        v-for="model in models"
        :key="model.id"
        v-slot="{ selected, active }"
        :value="model"
        as="template"
      >
        <li
          class="relative flex items-center pl-3 pr-9 py-2 border border-transparent transition-colors cursor-pointer select-none"
          :class="{ 'bg-blue-900/50': active }"
        >
          <img
            v-if="model.thumbnail"
            :src="model.thumbnail"
            class="mr-2 rounded object-cover"
            :class="[thumbnailWidthClass, thumbnailHeightClass]"
          >
          <slot v-else name="placeholder">
            <ImagePlaceholder
              class="mr-2 rounded"
              :class="[thumbnailWidthClass, thumbnailHeightClass]"
            />
          </slot>
          <span class="truncate">
            {{ model.title }}
          </span>
          <SelectSelectedIcon v-if="selected" :active="active" />
        </li>
      </ListboxOption>
    </ListboxOptions>
  </Listbox>
</template>

<script setup lang="ts">
export interface Model extends Record<string, any> {
  id: number | string
  title: string
  thumbnail?: string | null
}

withDefaults(defineProps<{
  models: Model[]
  selectMessage?: string
  thumbnailWidthClass?: string
  thumbnailHeightClass?: string
}>(), {
  selectMessage: '請選擇...',
  thumbnailWidthClass: 'w-10',
  thumbnailHeightClass: 'h-10',
})

const selected = defineModel<Model | null>({ required: true })
</script>
