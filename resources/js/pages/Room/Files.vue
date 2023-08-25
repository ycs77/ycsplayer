<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">

    <div class="grid grid-cols-12 gap-[--layout-gap] lg:gap-[--layout-gap-lg]">

      <!-- 導覽列 -->
      <RoomNavbar class="col-span-12" :room-id="room.id" />

      <div class="col-span-12">
        <div class="max-w-screen-md mx-auto bg-blue-950/50 p-4 rounded-lg lg:p-6">
          <div class="flex justify-between items-center">
            <h1 class="text-2xl">媒體檔案</h1>
            <button type="button" class="btn btn-primary">
              <HeroiconsDocumentText class="w-4 h-4 mr-1" />
              上傳檔案
            </button>
          </div>
          <div class="mt-6">
            <ul class="space-y-6">
              <li v-for="file in 10">
                <div class="flex justify-between items-center">
                  <div class="flex items-center">
                    <img
                      src="/media/色違いの翼.jpg"
                      class="w-32 rounded-lg mr-2"
                    />
                    <div>色違いの翼.mp4</div>
                  </div>

                  <div>
                    <button type="button" class="btn btn-sm btn-danger">
                      刪除
                    </button>
                  </div>
                </div>
              </li>
            </ul>

            <!-- <Pagination :collection="collection" /> -->
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import type { Room } from '@/types'

defineProps<{
  room: Required<Room>
}>()
</script>
