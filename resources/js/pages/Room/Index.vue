<template>
  <div>
    <div class="container py-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <div>
        <Link href="/rooms/video" class="block p-4 border border-gray-200 hover:border-sky-500 rounded-md">
          <h5 class="text-gray-700 text-xl font-bold">影片播放</h5>
        </Link>
      </div>

      <div>
        <Link href="/rooms/youtube" class="block p-4 border border-gray-200 hover:border-sky-500 rounded-md">
          <h5 class="text-gray-700 text-xl font-bold">YT播放</h5>
        </Link>
      </div>

      <div>
        <Link href="/rooms/audio" class="block p-4 border border-gray-200 hover:border-sky-500 rounded-md">
          <h5 class="text-gray-700 text-xl font-bold">音樂播放</h5>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

</script>
