<template>
  <div>
    <div class="container py-8">
      <div class="mb-4">
        <Link href="/rooms" class="text-lg text-sky-500">返回列表</Link>
      </div>

      <Player :type="playerType" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { PlayerType } from '@/types'

defineProps<{
  playerType: PlayerType
}>()
</script>
