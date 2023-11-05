<template>
  <CardAuth title="刪除帳號">
    <div>
      <div>請注意！此操作不可恢復，刪除帳號後所有帳號資料將被刪除！請謹慎操作。</div>

      <button type="button" class="mt-4 btn btn-danger" @click="deleteAccount">
        確定刪除帳號
      </button>
    </div>
  </CardAuth>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

defineProps<{
  email: string
}>()

function deleteAccount() {
  router.delete('/user')
}
</script>
