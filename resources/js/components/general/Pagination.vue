<template>
  <div v-if="pages.length > 1" class="font-medium">
    <div class="flex justify-between md:hidden">
      <template v-for="link in [previous, next]" :key="link.label">
        <template v-if="link">
          <Link v-if="link.isActive && link.url" class="pagination-item pagination-link" :href="link.url">{{ link.label }}</Link>
          <div v-else class="pagination-item pagination-disabled">{{ link.label }}</div>
        </template>
      </template>
    </div>

    <div class="hidden md:flex md:flex-wrap md:justify-center">
      <template v-for="link in items" :key="link.label">
        <div v-if="link.isCurrent" class="pagination-item pagination-active mr-2 mt-2">{{ link.label }}</div>
        <Link v-else-if="link.isActive && link.url" class="pagination-item pagination-link mr-2 mt-2" :href="link.url">{{ link.label }}</Link>
        <div v-else class="pagination-item pagination-disabled mr-2 mt-2">{{ link.label }}</div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePaginator } from 'momentum-paginator'

const props = defineProps<{
  collection: Paginator<Record<string, any>>
}>()

const { pages, items, previous, next } = usePaginator(props.collection)
</script>
