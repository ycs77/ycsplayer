<template>
  <ul>
    <li v-for="item in playlistItems" :key="item.id">
      <div
        v-if="item.id === currentPlaying?.id"
        class="flex items-center p-2 w-full bg-blue-800/50 border-l-4 border-blue-500 text-left select-none"
      >
        <img
          v-if="item.thumbnail"
          :src="item.thumbnail"
          class="w-28 max-w-[40%] shrink-0 rounded-lg aspect-video object-cover mr-2"
        />
        <MediaPlaceholder v-else class="w-28 max-w-[40%] shrink-0 mr-2" />
        <div class="break-all">{{ item.title }}</div>
      </div>

      <button
        v-else
        type="button"
        class="flex items-center p-2 w-full bg-blue-950/50 hover:bg-blue-900/50 border-l-4 border-transparent text-left transition-colors select-none"
        @click="$emit('selectItem', item)"
      >
        <img
          v-if="item.thumbnail"
          :src="item.thumbnail"
          class="w-28 max-w-[40%] shrink-0 rounded-lg aspect-video object-cover mr-2"
        />
        <MediaPlaceholder v-else class="w-28 max-w-[40%] shrink-0 mr-2" />
        <div class="break-all">{{ item.title }}</div>
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
