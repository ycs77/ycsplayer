<template>
  <div class="flex items-center gap-2 overflow-x-auto overflow-y-hidden">
    <RoomNavbarButton
      :active="tab === 'main'"
      @click="tab = 'main'"
    >
      <template #icon><HeroiconsPlayCircle class="w-4 h-4" /></template>
      首頁
    </RoomNavbarButton>

    <RoomNavbarButton
      :active="tab === 'chat'"
      @click="tab = 'chat'"
    >
      <template #icon><HeroiconsChatBubbleOvalLeftEllipsis class="w-4 h-4" /></template>
      聊天
      <RoomNavbarButtonDot v-if="chatUnread" />
    </RoomNavbarButton>

    <RoomNavbarButton
      v-if="canUploadMedias"
      :active="tab === 'medias'"
      @click="tab = 'medias'"
    >
      <template #icon><HeroiconsDocumentText class="w-4 h-4" /></template>
      檔案
    </RoomNavbarButton>

    <RoomNavbarButton
      v-if="canSettings"
      :active="tab === 'settings'"
      @click="tab = 'settings'"
    >
      <template #icon><HeroiconsCog6Tooth class="w-4 h-4" /></template>
      設定
    </RoomNavbarButton>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  chatUnread?: boolean
  canUploadMedias?: boolean
  canSettings?: boolean
}>(), {
  canUploadMedias: true,
  canSettings: true,
})

const tab = defineModel<string>('tab', { required: true })
</script>
