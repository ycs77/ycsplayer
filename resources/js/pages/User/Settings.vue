<template>
  <div class="px-[--layout-gap] pb-[--layout-gap] lg:px-[--layout-gap-lg] lg:pb-[--layout-gap-lg]">
    <Card title="帳號設定">
      <form @submit.prevent="submit">
        <div class="grid gap-x-4 gap-y-6 sm:grid-cols-2">
          <ImageUpload
            v-if="can.uploadAvatar"
            id="avatar"
            v-model="avataForm.avatar"
            :loading="avataForm.processing"
            :default-image="user.avatar ?? userPlaceholderSrc"
            :removeable="!!user.avatar"
            wrapper-class="text-center sm:col-span-2"
            image-class="w-32 h-32 rounded-full mx-auto"
            center-button
            @selected="selectedAvatar"
            @remove="removeAvatar"
          />
          <div v-else class="sm:col-span-2">
            <img
              :src="user.avatar ?? userPlaceholderSrc"
              class="object-cover w-32 h-32 rounded-full mx-auto"
            >
          </div>
          <TextInput id="name" v-model="form.name" label="姓名" />
          <TextInput id="email" v-model="form.email" label="E-mail" type="email" />
          <template v-if="!passwordLess">
            <TextInput id="current_password" v-model="form.current_password" label="舊密碼" type="password" />
            <div class="hidden sm:block" />
            <TextInput id="password" v-model="form.password" label="新密碼" type="password" />
            <TextInput id="password_confirmation" v-model="form.password_confirmation" label="確認密碼" type="password" />
          </template>
        </div>

        <div class="mt-6">
          <button type="submit" class="btn btn-primary" :disabled="form.processing">
            保存
          </button>
        </div>
      </form>
    </Card>

    <Card title="刪除帳號" class="mt-8">
      <button type="button" class="btn btn-danger" @click="deleteAccount">
        刪除帳號
      </button>
    </Card>

    <CropImageModal
      v-model="showCropImageModal"
      title="剪裁頭像"
      :src="avatarPreviewSrc"
      :aspect-ratio="1 / 1"
      circle
      :loading="avataForm.processing"
      @cropped="croppedAvatar"
    />
  </div>
</template>

<script setup lang="ts">
import userPlaceholderSrc from '@/images/user.svg'

defineOptions({ inheritAttrs: false })

const props = defineProps<{
  user: {
    id: string
    name: string
    email: string
    avatar: string | null
  }
  passwordLess: boolean
  can: {
    uploadAvatar: boolean
  }
}>()

const showCropImageModal = ref(false)

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  current_password: '',
  password: '',
  password_confirmation: '',
})

const avataForm = useForm({
  avatar: null as File | null,
})

const avatarPreviewSrc = ref<string | undefined>(undefined)

function submit() {
  form.put('/user/profile-information', {
    only: [...globalOnly, 'user', 'auth'],
    preserveScroll: true,
    onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
    onError: () => form.reset('current_password', 'password', 'password_confirmation'),
  })
}

function selectedAvatar(file: File, dataUrl: string) {
  avataForm.avatar = null

  avatarPreviewSrc.value = dataUrl
  showCropImageModal.value = true
}

function croppedAvatar(blob: Blob) {
  avataForm.avatar = blob as any

  if (!props.can.uploadAvatar) return

  avataForm.post('/user/avatar', {
    only: [...globalOnly, 'user', 'auth'],
    preserveScroll: true,
    onSuccess: () => avataForm.reset('avatar'),
    onError: () => avataForm.reset('avatar'),
  })
}

function removeAvatar() {
  if (!props.can.uploadAvatar) return

  avataForm.delete('/user/avatar', {
    only: [...globalOnly, 'user', 'auth'],
    preserveScroll: true,
  })
}

function deleteAccount() {
  if (confirm('確定要刪除此帳號嗎? 此操作將無法恢復')) {
    router.get('/user/destroy/confirm')
  }
}
</script>
