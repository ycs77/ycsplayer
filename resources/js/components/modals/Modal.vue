<template>
  <VueFinalModal
    v-model="show"
    content-class="absolute inset-0 px-4 py-10 lg:py-20 overflow-y-auto"
    content-transition="vfm-fade"
    overlay-transition="vfm-fade"
    :click-to-close="false"
    :esc-to-close="false"
  >
    <div
      class="relative mx-auto bg-gray-950 rounded-md"
      :class="maxWidthClass"
    >
      <div class="bg-blue-950/50 p-6 rounded-md">
        <div :class="closeButton ? 'mr-8' : ''">
          <slot name="title">
            <h5 class="flex items-center text-lg font-medium">
              <slot name="icon" />{{ title }}
            </h5>
          </slot>
        </div>

        <div>
          <slot />
        </div>

        <div>
          <slot name="footer" />
        </div>

        <button
          v-if="closeButton"
          type="button"
          class="absolute top-0 right-0 mt-4 mr-4 rounded transition-colors duration-100 disabled:text-gray-300 focus:ring-2 focus:ring-sky-500 focus:outline-none"
          :disabled="closeButtonDisabled"
          @click="show = false"
        >
          <MdiClose class="w-8 h-8" />
        </button>
      </div>
    </div>
  </VueFinalModal>
</template>

<script setup lang="ts">
import { VueFinalModal } from 'vue-final-modal'

withDefaults(defineProps<{
  title?: string
  maxWidthClass?: string
  closeButton?: boolean
  closeButtonDisabled?: boolean
}>(), {
  maxWidthClass: '',
  closeButton: true,
  closeButtonDisabled: false,
})

const show = defineModel<boolean>({ required: true })

defineOptions({ inheritAttrs: false })
</script>
