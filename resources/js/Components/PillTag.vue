<script setup>
import {computed} from 'vue'
import {colorsBgLight, colorsOutline} from '@/colors.js'
import BaseIcon from '@/Components/BaseIcon.vue'

const emit = defineEmits([
  'close'
])

const props = defineProps({
  text: {
    type: String,
    required: true
  },
  color: {
    type: String,
    required: false
  },
  type: {
    type: String,
    required: false
  },
  icon: {
    type: String,
    default: null
  },
  small: Boolean,
  outline: Boolean,
  canClose: {
    type: Boolean,
    default: false,
  }
})

const componentClass = computed(() => (
  [
    'border',
    props.small ? 'py-0.5 px-2 text-xs rounded-lg mr-1.5' : 'py-2 px-4 rounded-2xl mr-3',
    !props.type ? '' : (props.outline ? colorsOutline[props.type] : colorsBgLight[props.type])
  ]
))

const componentStyle = computed(() => {
  if (!props.color) {
    return null;
  }
  if (props.outline) {
    return {
      border: `1px solid ${props.color}`
    }
  } else {
    return {
      color: 'white',
      backgroundColor: props.color
    }
  }
})

const onClose = () => {
  emit('close')
}
</script>

<template>
  <div
    class="inline-flex items-center last:mr-0 capitalize"
    :class="componentClass"
    :style="componentStyle"
  >
    <BaseIcon
      v-if="icon"
      :path="icon"
      h="h-4"
      w="w-4"
      class="mr-2"
    />
    <span>{{ text }}</span>
    <div v-if="canClose" class="ml-4 h-6 w-6 rounded-full bg-white bg-opacity-25 hover:bg-opacity-40 cursor-pointer"
         @click="onClose">
      <BaseIcon :path="Icons.close"/>
    </div>
  </div>
</template>
