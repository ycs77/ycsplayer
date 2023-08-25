<template>
  <VueFinalModal
    classes="px-4 py-10 overflow-y-auto lg:py-20"
    overlay-class="!bg-black/30"
    :content-class="[
      'mx-auto relative bg-blue-950 p-6 rounded-md',
      maxWidthClass,
    ]"
    focus-trap
    :click-to-close="false"
    v-slot="{ params, close }"
    v-bind="$attrs"
  >
    <div :class="closeButton ? 'mr-8' : ''">
      <slot name="title" :params="params">
        <h5 class="flex items-center text-lg font-medium">
          <slot name="icon" />{{ title }}
        </h5>
      </slot>
    </div>

    <div>
      <slot :params="params" :close="close"></slot>
    </div>

    <div>
      <slot name="footer" :params="params" :close="close"></slot>
    </div>

    <button
      v-if="closeButton"
      type="button"
      class="absolute top-0 right-0 mt-4 mr-4 rounded transition-colors duration-100 disabled:text-gray-300 focus:ring-2 focus:ring-sky-500 focus:outline-none"
      :disabled="closeButtonDisabled"
      @click="close"
    >
      <MdiClose class="w-8 h-8" />
    </button>
  </VueFinalModal>
</template>

<script setup lang="ts">
defineOptions({ inheritAttrs: false })

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
</script>
