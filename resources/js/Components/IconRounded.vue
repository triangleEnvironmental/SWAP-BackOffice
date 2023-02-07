<script setup>
import { computed } from 'vue'
import { colorsText, colorsBgLight } from '@/colors.js'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  type: {
    type: String,
    required: true
  },
  w: {
    type: String,
    default: 'w-12'
  },
  h: {
    type: String,
    default: 'h-12'
  },
  bg: Boolean
})

const iconStyle = computed(
  () => props.bg ? colorsBgLight[props.type] : colorsText[props.type]
)
</script>

<template>
  <BaseIcon
    :path="icon"
    :w="w"
    :h="h"
    size="24"
    class="rounded-full"
    :class="bg ? colorsBgLight[type] : [colorsText[type], 'bg-gray-50 dark:bg-slate-800']"
  />
</template>
