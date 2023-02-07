<template>
  <v-select v-model="value"
            :label="label"
            :reduce="reduce"
            :multiple="multiple"
            :closeOnSelect="!multiple"
            autocomplete
            :disabled="readonly"
            :readonly="readonly"
            :components="components"
            :placeholder="readonly ? '(Empty)' : placeholder"
            :deselectFromDropdown="true"
            :options="options">
    <template v-slot:option="option">
      <div class="flex justify-between">
        <span class="truncate">
          {{ option[label] }}
        </span>
        <button v-if="Array.isArray(value) && value.includes(reduce ? reduce(option) : option)"
                class="btn-remove-option text-red-500 dark:text-red-400">
          remove
        </button>
      </div>
    </template>
    <template v-slot:no-options="{ search, searching }">
      <template v-if="searching">
        No results found for <em>{{ search }}</em
      >.
      </template>
      <em v-else style="opacity: 0.5">Start typing to search.</em>
    </template>
  </v-select>
</template>

<script setup>

import {watch, ref, shallowRef, h, computed} from "vue";

const props = defineProps({
  readonly: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: [Object, String, Number],
    default: null
  },
  placeholder: {
    type: String,
    default: 'Type to search',
  },
  multiple: {
    type: Boolean,
    default: false
  },
  label: {
    type: String,
    default: null
  },
  reduce: {
    type: Function,
    default: null
  },
  options: {
    type: Array,
    default: null,
  }
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

const components = shallowRef({
  Deselect: {
    render: () => h('div', {
      innerHTML: '<svg width="12" height="12" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><g><path d="M2.62,87.99L40.18,50L2.62,12.01C-0.8,8.88-0.87,5.68,2.4,2.41s6.47-3.2,9.6,0.21L50,40.19L87.99,2.62c3.13-3.42,6.33-3.49,9.6-0.21s3.2,6.47-0.21,9.6L59.82,50l37.57,37.98c3.42,3.13,3.49,6.33,0.21,9.6s-6.47,3.2-9.6-0.21L50,59.81L12.01,97.38c-3.13,3.42-6.33,3.49-9.6,0.21S-0.8,91.12,2.62,87.99z"></path></g></svg>'
    }),
  },
  OpenIndicator: {
    render: () => h('div', {
      innerHTML: '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="#6b7280" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 8l4 4 4-4"/></svg>'
    }),
  },
})

watch(() => props.modelValue, (newVal) => {
  emit('change', newVal)
})

// watch(() => value, (newVal) => {
//   emit("update:modelValue", newVal)
// })

// const removeSelectedOption = (option) => {
//   const index = props.modelValue.indexOf(props.reduce ? props.reduce(option) : option);
//   console.log(index)
//   if (index > -1) {
//     props.modelValue.splice(index, 1)
//     emit('update:modelValue', [...props.modelValue])
//   }
// }

</script>

<style>
:root {
  --vs-controls-color: #6b7280;
  --vs-border-color: rgb(55 65 81);

  --vs-dropdown-bg: #f7f7f7;
  --vs-dropdown-color: black;
  --vs-dropdown-option-color: black;

  --vs-selected-bg: #e9efff;
  --vs-selected-border-color: transparent;
  --vs-selected-color: rgb(59 130 246);

  --vs-search-input-color: black;

  --vs-dropdown-option--active-bg: rgb(59 130 246);
  --vs-dropdown-option--active-color: #eeeeee;
  --vs-dropdown-option-padding: 8px 20px;
}

:root .dark {
  --vs-search-input-color: rgb(243 244 246);
  --vs-dropdown-bg: rgb(31 41 55);
  --vs-dropdown-color: rgb(243 244 246);
  --vs-selected-bg: rgb(17 24 39);
  --vs-dropdown-option-color: rgb(243 244 246);
}
</style>
