<template>
  <ul>
    <li v-for="item in playlistItems" :key="item.id">
      <div
        v-if="item.id === currentPlaying?.id"
        class="block px-4 py-2 w-full bg-blue-800/50 border-l-4 border-blue-500 text-left select-none"
      >
        {{ item.title }}
      </div>
      <button
        v-else
        type="button"
        class="px-4 py-2 w-full bg-blue-950/50 hover:bg-blue-900/50 border-l-4 border-transparent text-left transition-colors select-none"
        @click="$emit('selectItem', item)"
      >
        {{ item.title }}
      </button>
    </li>
  </ul>
</template>

<script setup lang="ts">
import type { PlaylistItem } from '@/types'

defineProps<{
  currentPlaying: PlaylistItem | null
  playlistItems: PlaylistItem[]
}>()

defineEmits<{
  selectItem: [PlaylistItem]
}>()
</script>
