<template>
  <div class="font-medium">
    <div class="flex justify-between md:hidden">
      <template v-for="link in simpleLinks">
        <Component
          :is="link.url ? 'Link' : 'div'"
          :href="link.url"
          class="pagination-item"
          :class="link.url ? 'pagination-link' : 'pagination-disabled'"
        >
          {{ link.label }}
        </Component>
      </template>
    </div>

    <div class="hidden md:flex md:flex-wrap">
      <template v-for="link in links">
        <Component
          :is="!link.active && link.url ? 'Link' : 'div'"
          :href="link.url"
          class="pagination-item mr-2 mt-2"
          :class="
            link.active
              ? 'pagination-active'
              : (link.url ? 'pagination-link' : 'pagination-disabled')
          "
        >
          {{ link.label }}
        </Component>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  links: {
    url: string
    label: string
    active: boolean
  }[]
}>()

const simpleLinks = computed(() => [
  props.links.slice(0).shift()!,
  props.links.slice(-1).pop()!,
])
</script>
