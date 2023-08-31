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

    <div class="mt-2 text-blue-300 space-y-2">
      <div>成員數：{{ membersCount }}人</div>

      <div>
        <TextareaInput
          v-if="form.editing"
          ref="inputRef"
          id="note"
          v-model="form.note"
          rows="3"
          class="mt-2"
          @keydown.enter.prevent="endEditingNote"
          @blur="endEditingNote"
        />

        <div v-else class="flex items-center">
          <div
            v-if="form.note"
            class="break-all"
            :class="{ 'line-clamp-3': !showFull }"
            :title="showFull ? '顯示部分' : '顯示全部'"
            @click="showFull = !showFull"
          >
            {{ form.note }}
          </div>
          <div v-else class="text-blue-400/50">
            記事本可以紀錄一些內容...
          </div>

          <button
            type="button"
            class="link"
            @click="startEditingNote"
          >
            <HeroiconsPencilSquare class="w-5 h-5 ml-1" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Room } from '@/types'

const props = defineProps<{
  room: Room
  membersCount: number
}>()

const emit = defineEmits<{
  submitNote: []
}>()

const inputRef = ref<{ $el: HTMLDivElement }>()

const showFull = ref(false)

const form = useForm({
  note: props.room.note ?? '',
  editing: false,
})

function focusNoteInput() {
  nextTick(() => {
    inputRef.value?.$el.querySelector('textarea')?.focus()
  })
}

function startEditingNote() {
  form.editing = true
  focusNoteInput()
}

function endEditingNote() {
  form.put(`/rooms/${props.room.id}/note`, {
    only: [...globalOnly, 'room'],
    preserveScroll: true,
    onSuccess() {
      emit('submitNote')
      form.editing = false
    },
    onError() {
      focusNoteInput()
    },
  })
}
</script>
