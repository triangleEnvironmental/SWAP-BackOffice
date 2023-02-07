<script setup>
import {computed} from 'vue'
import {mdiClose} from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import OverlayLayer from '@/Components/OverlayLayer.vue'

const props = defineProps({
  title: {
    type: String,
    default: null
  },
  largeTitle: {
    type: String,
    default: null
  },
  icon: {
    type: String,
    default: null
  },
  button: {
    type: String,
    default: 'info'
  },
  buttonLabel: {
    type: String,
    default: 'Done'
  },
  hasButton: {
    type: Boolean,
    default: true,
  },
  hasCancel: Boolean,
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  },
  small: {
    type: Boolean,
    default: true,
  },
  closeOnDone: {
    type: Boolean,
    default: true,
  },
  showSettingButton: {
    type: Boolean,
    default: false,
  },
  headerIcon: {
    type: String,
    default: null,
  },
  onClickSetting: {
    type: Function,
    default: null
  },
  settingTooltip: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'cancel', 'confirm'])

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

const confirmCancel = mode => {
  emit(mode)
}

const confirm = () => {
  if (props.closeOnDone) {
    close()
  }
  confirmCancel('confirm')
}

const cancel = () => {
  close()
  confirmCancel('cancel')
}

const onClickHeader = () => {
  if (props.onClickSetting != null) {
    props.onClickSetting()
  } else {
    cancel()
  }
}

const close = () => {
  value.value = false
}

defineExpose({
  close,
})
</script>

<template>
  <OverlayLayer
    v-show="value"
    @overlay-click="cancel"
  >
    <CardBox
      v-show="value"
      :title="title"
      class="card-box-modal flex flex-col shadow-lg w-full max-h-modal z-50 overflow-y-auto"
      :class="{
        'md:w-3/5 lg:w-2/5': small,
        'md:w-4/5 lg:w-4/5': !small,
      }"
      :header-icon="headerIcon"
      :show-setting-button="showSettingButton"
      modal
      :icon="icon"
      @header-icon-click="onClickHeader"
      :setting-tooltip="settingTooltip"
    >
      <template v-slot:default>
        <div class="grow-1 space-y-3">
          <h1
            v-if="largeTitle"
            class="text-2xl"
          >
            {{ largeTitle }}
          </h1>
          <slot/>
        </div>
      </template>

      <template v-slot:actions>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
          <BaseButtons>
            <BaseButton
              v-if="hasCancel"
              label="Cancel"
              :color="button"
              outline
              @click="cancel"
            />
            <slot name="buttons"></slot>
            <BaseButton
              v-if="hasButton"
              :label="buttonLabel"
              :color="button"
              @click="confirm"
            />
          </BaseButtons>
        </div>
      </template>
    </CardBox>
  </OverlayLayer>
</template>
