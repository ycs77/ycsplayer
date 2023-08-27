<template>
  <div class="flex items-center gap-2 overflow-x-auto overflow-y-hidden">
    <RoomNavbarButton href="/rooms">
      <template #icon><HeroiconsHome class="w-4 h-4" /></template>
      房間列表
    </RoomNavbarButton>

    <RoomNavbarButton
      :href="`/rooms/${roomId}`"
      :active="$page.url === `/rooms/${roomId}`"
    >
      <template #icon><HeroiconsPlayCircle class="w-4 h-4" /></template>
      播放器
    </RoomNavbarButton>

    <RoomNavbarButton
      :href="`/rooms/${roomId}/members`"
      :active="$page.url === `/rooms/${roomId}/members`"
      class="xl:hidden"
    >
      <template #icon><HeroiconsUserGroup class="w-4 h-4" /></template>
      成員
    </RoomNavbarButton>

    <RoomNavbarButton
      v-if="canUploadMedias"
      :href="`/rooms/${roomId}/medias`"
      :active="$page.url === `/rooms/${roomId}/medias`"
    >
      <template #icon><HeroiconsDocumentText class="w-4 h-4" /></template>
      檔案
    </RoomNavbarButton>

    <RoomNavbarButton
      v-if="canSettings"
      :href="`/rooms/${roomId}/settings`"
      :active="$page.url === `/rooms/${roomId}/settings`"
    >
      <template #icon><HeroiconsCog6Tooth class="w-4 h-4" /></template>
      設定
    </RoomNavbarButton>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  roomId: string
  canUploadMedias?: boolean
  canSettings?: boolean
}>(), {
  canUploadMedias: true,
  canSettings: true,
})
</script>
