<template>
  <div class="bg-blue-950/50 p-4 rounded-lg">
    <h5>房間成員</h5>

    <ul class="mt-2 space-y-1 -mx-2 -mb-2">
      <li v-for="member in members">
        <button
          type="button"
          class="p-2 w-full flex items-center hover:bg-blue-900/50 rounded-lg transition-colors"
          @click="openMemberModal(member)"
        >
          <img
            class="w-8 h-8 rounded-full mr-2"
            :src="member.avatar ?? '/images/user.svg'"
          />

          <div class="flex items-center h-10">
            <div>
              <div class="tracking-wide">
                {{ member.name }}
                <RoomRoleBadge v-if="member.role === 'admin'" role="admin" class="ml-1" />
              </div>
              <div v-if="member.online" class="flex items-center text-xs">
                <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1" /><span class="text-blue-300">上線</span>
              </div>
            </div>
          </div>
        </button>
      </li>
    </ul>

    <div v-if="canInvite" class="mt-4 -mx-2 -mb-2">
      <button
        type="button"
        class="flex justify-center items-center p-2 w-full hover:bg-blue-900/40 text-center rounded-lg transition-colors select-none"
        @click="openMemberInviteModal"
      >
        <HeroiconsUserPlus class="w-4 h-4 mr-2" />
        邀請新成員
      </button>
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
      :can-remove="canRemove"
      @remove="removeMember"
    />
  </div>
</template>

<script setup lang="ts">
import type { RoomMember } from '@/types'

withDefaults(defineProps<{
  roomId: string
  members: RoomMember[]
  canInvite?: boolean
  canRemove?: boolean
}>(), {
  canInvite: true,
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
</script>
