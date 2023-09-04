<template>
  <Modal
    v-model="show"
    title="房間成員"
    max-width-class="max-w-[560px] w-full"
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
          >
        </div>

        <h4 class="mt-3 text-2xl text-center">{{ member.name }}</h4>
        <div class="mt-1.5 flex justify-center gap-1">
          <RoomRoleBadge :role="member.role" size="lg" />
        </div>

        <div v-if="canChangeRole">
          <Field
            v-if="user?.id !== member.id"
            label="用戶角色"
            :error="$page.props.errors.role"
            class="mt-6"
          >
            <Select v-model="role" :options="roles">
              <template #option="{ option }">
                <div class="text-left select-none">
                  <div>{{ option.label }}</div>
                  <div class="mt-1 text-sm text-gray-300/50">{{ option.description }}</div>
                </div>
              </template>
            </Select>
          </Field>
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
import { roles } from '@/const'
import type { RoomMember } from '@/types'

const props = withDefaults(defineProps<{
  roomId: string
  member: RoomMember | undefined
  canChangeRole?: boolean
  canRemove?: boolean
}>(), {
  canChangeRole: true,
  canRemove: true,
})

const emit = defineEmits<{
  remove: [member: RoomMember]
}>()

const show = defineModel<boolean>({ required: true })

const { user } = useAuth()

const role = ref(roles[1])

const { ignoreUpdates } = watchIgnorable(
  [show, role], ([newShow, newRole], [prevShow, prevRole]) => {
    if (newShow && prevShow && newRole !== prevRole) {
      router.patch(`/rooms/${props.roomId}/members/${props.member?.id}/role`, {
        role: newRole.value,
      }, {
        only: [...globalOnly, 'members'],
        preserveScroll: true,
      })
    }
  }
)

watch(show, (newShow, prevShow) => {
  if (newShow && !prevShow) {
    ignoreUpdates(() => {
      role.value = roles.find(role => role.value === props.member?.role) ?? roles[1]
    })
  }
})

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
