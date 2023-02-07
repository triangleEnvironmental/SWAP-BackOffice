<template>
  <SelectForm class="min-w-48"
              v-model="value"
              :label="label"
              :placeholder="placeholder"
              :reduce="reduce"
              :multiple="multiple"
              :options="options"
              @change="onChange"/>
</template>

<script setup>
import SelectForm from '@/Components/SelectForm.vue'
import {computed} from "vue";

const props = defineProps({
  options: {
    type: Array,
    required: true,
  },
  reduce: {
    type: Function,
    default: (data) => `${data.id}`
  },
  multiple: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Select a filter'
  },
  label: {
    type: String,
    default: 'name_en'
  },
  modelValue: {
    type: [Object, String, Number],
    default: null
  },
})

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const emit = defineEmits([
  'update:modelValue',
  'change'
])

const onChange = (data) => {
  emit('change', data)
}
</script>

<style scoped>

</style>
