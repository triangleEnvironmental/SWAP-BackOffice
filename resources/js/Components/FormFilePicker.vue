<script setup>
import {mdiUpload} from '@mdi/js'
import {computed, ref, watch} from 'vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
  modelValue: {
    type: [Object, File, Array],
    default: null
  },
  label: {
    type: String,
  },
  icon: {
    type: String,
    default: mdiUpload
  },
  accept: {
    type: String,
    default: null
  },
  color: {
    type: String,
    default: 'info'
  },
  outline: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
  },
  multiple: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const root = ref(null)

const getPreview = () => {
  return new Promise((resolve, reject) => {
    if (file.value) {
      const reader = new FileReader();

      reader.onload = (e) => {
        resolve(e.target.result);
      };

      reader.readAsDataURL(file.value);
    }
    return null;
  })
}

const file = ref(props.modelValue)

const modelValueProp = computed(() => props.modelValue)

watch(modelValueProp, value => {
  file.value = value

  if (!value) {
    if (root.value.input) {
      root.value.input.value = null
    } else {
      root.value.value = null
    }
  }
})

defineExpose({
  getPreview,
})

const upload = event => {
  const value = event.target.files || event.dataTransfer.files

  if (props.multiple) {
    file.value = value
    emit('update:modelValue', file.value)
  } else {
    file.value = value[0]
    emit('update:modelValue', file.value)
  }

  // Use this as an example for handling file uploads
  // let formData = new FormData()
  // formData.append('file', file.value)

  // const mediaStoreRoute = `/your-route/`

  // axios
  //   .post(mediaStoreRoute, formData, {
  //     headers: {
  //       'Content-Type': 'multipart/form-data'
  //     },
  //     onUploadProgress: progressEvent
  //   })
  //   .then(r => {
  //
  //   })
  //   .catch(err => {
  //
  //   })
}

// const uploadPercent = ref(0)
//
// const progressEvent = progressEvent => {
//   uploadPercent.value = Math.round(
//     (progressEvent.loaded * 100) / progressEvent.total
//   )
// }
</script>

<template>
  <div class="flex items-stretch justify-start relative">
    <label class="inline-flex">
      <BaseButton
        as="a"
        :label="label"
        :icon="icon"
        :title="title"
        :color="color"
        :outline="outline"
        :class="{ 'rounded-r-none': modelValue && file }"
      />
      <input
        ref="root"
        type="file"
        :multiple="multiple"
        class="absolute top-0 left-0 w-full h-full opacity-0 outline-none cursor-pointer -z-1"
        :accept="accept"
        @input="upload"
      >
    </label>
    <div v-if="file && modelValue">
      <span
        class="inline-flex px-4 py-2 justify-center bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 border rounded-r"
      >
        {{ file.name }}
      </span>
    </div>
  </div>
</template>
