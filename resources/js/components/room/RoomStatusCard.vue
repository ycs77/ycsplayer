<template>
  <div class="bg-blue-950/50 p-4 rounded-lg">
    <div class="flex items-center">
      <RoomLogo
        :room="room"
        class="w-6 h-6 mr-1"
        color-class="text-blue-400/50"
      />
      <h1 class="text-xl">{{ room.name }}</h1>
    </div>

    <div class="mt-3 text-blue-300 space-y-2">
      <div>成員數：{{ membersCount }}人</div>

      <div>
        <div v-if="editing">
          <div class="relative">
            <TextareaInput
              id="note"
              ref="inputRef"
              v-model="noteForm.note"
              rows="3"
              class="block"
              @keydown.enter.prevent="saveNote"
              @keydown.esc="cancelEditNote"
            />

            <div v-if="noteForm.processing" class="absolute inset-0 flex justify-center items-center bg-blue-900/75 rounded-md">
              <Loading class="w-10 h-10 text-blue-600/50" />
            </div>
          </div>

          <div class="mt-2 flex justify-end items-center space-x-2">
            <button
              type="button"
              class="btn btn-sm btn-secondary"
              :disabled="noteForm.processing"
              @click="cancelEditNote"
            >
              取消
            </button>

            <button
              type="button"
              class="btn btn-sm btn-primary"
              :disabled="noteForm.processing"
              @click="saveNote"
            >
              保存
            </button>
          </div>
        </div>

        <div v-else>
          <div class="flex items-center">
            <div
              v-if="noteForm.note"
              class="break-all"
              :class="{ 'line-clamp-3': !showFull }"
              :title="showFull ? '顯示部分' : '顯示全部'"
              @click="showFull = !showFull"
            >
              {{ noteForm.note }}
            </div>
            <div v-else class="text-blue-400/50">
              記事本可以紀錄一些內容...
            </div>

            <button
              type="button"
              class="link disabled:text-blue-400/50"
              :disabled="!!editingUser"
              @click="startEditingNote"
            >
              <HeroiconsPencilSquare class="w-5 h-5 ml-1" />
            </button>
          </div>

          <div v-if="editingUser" class="mt-1 text-blue-400/50 text-sm">
            <span class="text-blue-400">{{ editingUser.name }}</span> 編輯中...
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios'
import type { Room } from '@/types'

const props = defineProps<{
  room: Room
  membersCount: number
  editingUser: {
    id: string
    name: string
  } | null
}>()

const emit = defineEmits<{
  submitNote: []
}>()

const inputRef = ref<{ $el: HTMLDivElement }>()

const showFull = ref(false)

const noteForm = useForm({
  note: props.room.note ?? '',
})
let originalNote = ''

const editing = ref(false)

function focusNoteInput() {
  nextTick(() => {
    inputRef.value?.$el.querySelector('textarea')?.focus()
  })
}

function startEditingNote() {
  editing.value = true
  originalNote = noteForm.note
  focusNoteInput()

  axios.post(`/rooms/${props.room.id}/note`)
}

function saveNote() {
  noteForm.put(`/rooms/${props.room.id}/note`, {
    only: [...globalOnly, 'room', 'editingUser'],
    preserveScroll: true,
    onSuccess() {
      editing.value = false
      originalNote = ''
      emit('submitNote')
    },
    onError() {
      focusNoteInput()
    },
  })
}

function cancelEditNote() {
  editing.value = false
  noteForm.note = originalNote

  axios.delete(`/rooms/${props.room.id}/note`)
}

watch(() => props.room, () => {
  noteForm.note = props.room.note ?? ''
})
</script>
