<template>
  <div>
    <div v-if="canAdd && !buttonOnBottom">
      <button
        type="button"
        class="btn btn-list-item"
        @click="$emit('clickAddItem')"
      >
        <HeroiconsPlus class="w-4 h-4 mr-2" />
        新增播放項目
      </button>
    </div>

    <ul>
      <li v-for="item in playlistItems" :key="item.id" ref="itemRefs">
        <PlaylistItem
          is="div"
          v-if="!canSelect"
          :item="item"
          :can-remove="false"
        />

        <PlaylistItem
          is="div"
          v-else-if="item.id === currentPlaying?.id"
          active
          :item="item"
          :can-remove="canRemove"
          @remove="$emit('removeItem', item)"
        />

        <PlaylistItem
          is="button"
          v-else
          type="button"
          :item="item"
          :can-remove="canRemove"
          @click="$emit('selectItem', item)"
          @remove="$emit('removeItem', item)"
        />
      </li>
    </ul>

    <div v-if="canAdd && buttonOnBottom">
      <button
        type="button"
        class="btn btn-list-item"
        @click="$emit('clickAddItem')"
      >
        <HeroiconsPlus class="w-4 h-4 mr-2" />
        新增播放項目
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { type PlaylistItem } from '@/types'

const props = withDefaults(defineProps<{
  currentPlaying: PlaylistItem | null
  playlistItems: PlaylistItem[]
  buttonOnBottom?: boolean
  canAdd?: boolean
  canSelect?: boolean
  canRemove?: boolean
}>(), {
  canAdd: true,
  canSelect: true,
  canRemove: true,
})

defineEmits<{
  clickAddItem: []
  selectItem: [PlaylistItem]
  removeItem: [PlaylistItem]
}>()

const itemRefs = ref<HTMLLIElement[]>([])

function scrollIntoCurrentItem() {
  if (props.currentPlaying) {
    for (const li of itemRefs.value) {
      const child = li.firstChild as Element | null
      if (child?.nodeName?.toLowerCase() === 'div') {
        child.scrollIntoView()
        break
      }
    }
  }
}

defineExpose({ scrollIntoCurrentItem })
</script>
