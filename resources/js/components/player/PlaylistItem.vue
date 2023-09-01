<template>
  <component
    :is="is"
    class="flex items-center p-2 w-full border-l-4 text-left select-none"
    :class="{
      'bg-blue-800/50 border-blue-500': active,
      'bg-blue-950/50 hover:bg-blue-900/50 border-transparent transition-colors': !active,
    }"
  >
    <img
      v-if="item.thumbnail"
      :src="item.thumbnail"
      class="w-28 max-w-[40%] shrink-0 rounded-lg aspect-video object-cover mr-2"
    >
    <MediaPlaceholder v-else class="w-28 max-w-[40%] shrink-0 mr-2" />

    <div class="grow break-all">{{ item.title }}</div>

    <div v-if="canRemove" class="ml-1 shrink-0 whitespace-nowrap">
      <button
        type="button"
        class="btn btn-sm btn-danger"
        @click.stop="removeItem"
      >
        刪除
      </button>
    </div>
  </component>
</template>

<script setup lang="ts">
import type { PlaylistItem } from '@/types'

const props = withDefaults(defineProps<{
  is: string
  item: PlaylistItem
  active?: boolean
  canRemove?: boolean
}>(), {
  canRemove: true,
})

const emit = defineEmits<{
  remove: []
}>()

function removeItem() {
  if (confirm(`確定要切掉 ${props.item.title} 嗎?`)) {
    emit('remove')
  }
}
</script>
