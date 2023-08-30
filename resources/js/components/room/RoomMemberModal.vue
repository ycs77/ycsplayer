<template>
  <Modal
    title="房間成員"
    max-width-class="max-w-[560px] w-full"
    v-model="show"
  >
    <template #icon>
      <HeroiconsUser class="mr-1" />
    </template>

    <div v-if="member" class="mt-4 relative">
      <div class="py-6">
        <div class="flex justify-center">
          <img
            :src="member.avatar ?? '/images/user.svg'"
            class="w-20 h-20 rounded-full"
          />
        </div>

        <h4 class="mt-3 text-2xl text-center">{{ member.name }}</h4>
        <div class="mt-1.5 flex justify-center gap-1">
          <RoomRoleBadge :role="member.role" />
        </div>
      </div>

      <div v-if="canRemoveMember" class="mt-6 text-center">
        <button type="button" class="btn btn-danger" @click="removeMember">
          {{ user?.id === member.id ? '退出房間' : '退出成員' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import type { RoomMember } from '@/types'

const props = withDefaults(defineProps<{
  roomId: string
  member: RoomMember | undefined
  canRemove?: boolean
}>(), {
  canRemove: true,
})

const emit = defineEmits<{
  remove: [member: RoomMember]
}>()

const show = defineModel<boolean>({ required: true })

const { user } = useAuth()

const canRemoveMember = computed(() => {
  if (!props.member) return false
  if (!user.value) return false

  if (user.value.id === props.member.id) {
    if (props.member.role === 'admin') return false
    return true
  }

  return props.canRemove
})

function removeMember() {
  if (!props.member) return

  if (confirm(`確定要請 ${props.member.name} 離開房間嗎?`)) {
    router.delete(`/rooms/${props.roomId}/members/${props.member.id}`, {
      only: [...globalOnly, 'members'],
      preserveScroll: true,
      onSuccess() {
        emit('remove', props.member!)
      },
    })
  }
}
</script>
