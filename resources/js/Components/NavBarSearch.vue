<script setup>
import FormControl from '@/Components/FormControl.vue'
import useMounted from "@/Composibles/useMounted";
import BaseButton from '@/Components/BaseButton.vue'
import {computed} from "vue";

const emit = defineEmits([
  'update:modelValue',
  'submit'
])

const props = defineProps({
  placeholder: {
    type: String,
    default: "Search... (Ctrl + K)",
  },
  modelValue: {
    type: String,
  }
})

const computedValue = computed({
  get: () => props.modelValue,
  set: value => {
    emit('update:modelValue', value)
  }
})

const onSubmit = () => {
  emit('submit', computedValue.value)
}

const onClear = () => {
  computedValue.value = ''
  onSubmit()
}

const {isMounted} = useMounted()
</script>

<template>
  <Teleport v-if="isMounted" to="#search-bar">
    <div class="flex items-center">
      <FormControl
        ref="root"
        :placeholder="placeholder"
        ctrl-k-focus
        transparent
        borderless
        v-model="computedValue"
        @keyup.enter="onSubmit"
      />

      <BaseButton outline
                  v-if="computedValue"
                  class="ml-3"
                  :icon="Icons.close"
                  @click="onClear"/>
    </div>
  </Teleport>
</template>
