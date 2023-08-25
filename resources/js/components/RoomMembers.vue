<template>
  <div class="bg-blue-950/50 p-4 rounded-lg">
    <h5 class="font-semibold">房間成員</h5>
    <ul class="mt-2 space-y-1 -mx-2 -mb-2">
      <li v-for="user in 4">
        <button type="button" class="p-2 w-full flex items-center hover:bg-blue-900/50 rounded-lg transition-colors">
          <img
            class="w-8 h-8 rounded-full mr-2"
            src="/images/user.svg"
            alt=""
          />
          <div class="flex items-center h-10">
            <div>
              <div class="font-normal tracking-wide">Lucas Yang</div>
              <div v-if="user <= 3" class="flex items-center text-xs">
                <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1" />上線
              </div>
            </div>
          </div>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">

</script>
