<template>
  <PlayerVideo
    v-if="type === PlayerType.Video || type === PlayerType.YouTube"
    :type="type"
    :src="src"
    :poster="poster"
  />

  <PlayerAudio
    v-else-if="type === PlayerType.Audio"
    :type="type"
    :src="src"
  />
</template>

<script setup lang="ts">
import { PlayerType } from '@/types'

const props = defineProps<{
  type: PlayerType
}>()

const src = computed(() => {
  if (props.type === PlayerType.Video)
    return '/media/色違いの翼.mp4'
  if (props.type === PlayerType.Audio)
    return '/media/色違いの翼.mp3'
  if (props.type === PlayerType.YouTube)
    return 'https://www.youtube.com/watch?v=Melo0YFiDSY'
  return ''
})

const poster = computed(() => {
  if (props.type === PlayerType.Video)
    return '/media/色違いの翼.jpg'
  if (props.type === PlayerType.Audio)
    return '/media/色違いの翼.jpg'
})
</script>
