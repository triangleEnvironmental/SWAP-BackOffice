<script setup>
import {computed, useSlots} from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'

defineProps({
  label: {
    type: String,
    default: null
  },
  required: {
    type: Boolean,
    default: false
  },
  labelFor: {
    type: String,
    default: null
  },
  icon: {
    type: String,
    default: null
  },
  help: {
    type: String,
    default: null
  },
  error: {
    type: String,
    default: null
  }
})

const slots = useSlots()

const wrapperClass = computed(() => {
  const base = []
  const slotsLength = slots.default().length

  if (slotsLength > 1) {
    base.push('grid grid-cols-1 gap-3')
  }

  if (slotsLength === 2) {
    base.push('md:grid-cols-2')
  }

  return base
})
</script>

<template>
  <div class="mb-6 last:mb-0">
    <label
      v-if="label"
      :for="labelFor"
      class="block font-bold mb-2"
    >
      <span class="flex items-center">
        <template v-if="icon">
          <BaseIcon :path="icon" class="mr-1"/>
        </template>
        <span :class="{'required': required}">
          {{ label }}
        </span>
      </span>
    </label>
    <div :class="wrapperClass">
      <slot/>
    </div>
    <div
      v-if="help"
      class="text-xs text-gray-500 dark:text-gray-400 mt-1"
    >
      <span class="text-red-500">*</span> {{ help }}
    </div>
    <div
      v-if="error"
      class="text-xs text-red-500 dark:text-red-400 mt-1"
    >
      {{ error }}
    </div>
  </div>
</template>
