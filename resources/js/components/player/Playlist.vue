<template>
  <div>
    <ul>
      <li v-for="item in playlistItems" :key="item.id">
        <PlaylistItem
          v-if="item.id === currentPlaying?.id"
          is="div"
          active
          :item="item"
          :can-remove="canRemove"
          @remove="$emit('removeItem', item)"
        />

        <PlaylistItem
          v-else
          is="button"
          type="button"
          :item="item"
          :can-remove="canRemove"
          @click="$emit('selectItem', item)"
          @remove="$emit('removeItem', item)"
        />
      </li>
    </ul>

    <div v-if="canAdd">
      <button
        type="button"
        class="flex justify-center items-center p-2 w-full bg-blue-950/50 hover:bg-blue-900/50 text-center transition-colors select-none"
        @click="$emit('openAddItem')"
      >
        <HeroiconsPlus class="w-4 h-4 mr-2" />
        新增播放項目
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PlaylistItem } from '@/types'

withDefaults(defineProps<{
  currentPlaying: PlaylistItem | null
  playlistItems: PlaylistItem[]
  canAdd?: boolean
  canRemove?: boolean
}>(), {
  canAdd: true,
  canRemove: true,
})

defineEmits<{
  openAddItem: []
  selectItem: [PlaylistItem]
  removeItem: [PlaylistItem]
}>()
</script>
