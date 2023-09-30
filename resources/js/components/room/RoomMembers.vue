<template>
  <div class="bg-blue-950/50 p-4 rounded-lg">
    <div class="-m-2 overflow-x-auto">
      <ul class="flex space-x-0.5">
        <li v-for="member in members" :key="member.id">
          <button
            type="button"
            class="w-[55px] h-[70px] hover:bg-blue-900/50 rounded-lg transition-colors"
            @click="openMemberModal(member)"
          >
            <div class="relative w-9 h-9 mx-auto mb-2">
              <Avatar class="w-9 h-9" :src="member.avatar" />
              <div
                v-if="member.online"
                class="absolute bottom-0 right-0 w-2 h-2 bg-green-400 rounded-full"
              />
            </div>
            <div class="text-xs tracking-wide truncate">{{ member.name }}</div>
          </button>
        </li>

        <li v-if="canInvite" class="flex justify-center items-center">
          <button
            type="button"
            class="flex justify-center items-center p-2 w-full hover:bg-blue-900/40 text-center rounded-lg transition-colors select-none"
            title="邀請新成員"
            @click="openMemberInviteModal"
          >
            <HeroiconsUserPlus class="w-4.5 h-4.5" />
          </button>
        </li>
      </ul>
    </div>

    <RoomMemberInviteModal
      v-model="showMemberInviteModal"
      :room-id="roomId"
      @invite="inviteMember"
    />

    <RoomMemberModal
      v-model="showMemberModal"
      :room-id="roomId"
      :member="memberDetail"
      :can-change-role="canChangeRole"
      :can-remove="canRemove"
      @remove="removeMember"
    />
  </div>
</template>

<script setup lang="ts">
import type { RoomMember } from '@/types'

const props = withDefaults(defineProps<{
  roomId: string
  members: RoomMember[]
  canInvite?: boolean
  canChangeRole?: boolean
  canRemove?: boolean
}>(), {
  canInvite: true,
  canChangeRole: true,
  canRemove: true,
})

const showMemberInviteModal = ref(false)
const showMemberModal = ref(false)

const memberDetail = ref(undefined) as Ref<RoomMember | undefined>

function openMemberInviteModal() {
  showMemberInviteModal.value = true
}

function inviteMember() {
  showMemberInviteModal.value = false
}

function openMemberModal(member: RoomMember) {
  memberDetail.value = member
  showMemberModal.value = true
}

function removeMember() {
  showMemberModal.value = false
}

watch(() => props.members, () => {
  if (memberDetail.value) {
    memberDetail.value = props.members.find(member => member.id === memberDetail.value?.id)
  }
}, { deep: true })
</script>
