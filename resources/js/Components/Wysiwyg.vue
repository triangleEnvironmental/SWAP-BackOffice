<template>
  <!--  Use online builder https://ckeditor.com/ckeditor-5/online-builder/-->
  <div>
    <div v-if="unsavedData" class="text-right mb-2">
      <BaseButton :icon="mdiFileArrowUpDownOutline"
                  label="Load unsaved data"
                  small
                  @click="loadUnsavedData"/>
    </div>
    <div class="prose max-w-full">
      <div ref="editorRef"></div>
    </div>
  </div>
</template>

<script setup>
import {nextTick, onMounted, ref, watch} from "vue";
import "@/Plugins/Ckeditor5/build/ckeditor"
import {mdiFileArrowUpDownOutline} from "@mdi/js"
import {usePage} from "@inertiajs/inertia-vue3";
import BaseButton from "@/Components/BaseButton.vue"

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  formRole: {
    type: String,
    default: 'CKEDITOR_CONTENT'
  },
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  }
})

watch(() => props.modelValue, (newVal, oldVal) => {
  unsavedData.value = ""
  if (newVal !== currentData) {
    editor?.setData(props.modelValue)
  }
  if (props.modelValue === '') {
    localStorage.removeItem(props.formRole)
  }
})

const unsavedData = ref("")

const ClassicEditor = window.ClassicEditor

const editorRef = ref(null)
let editor = null
window.editor = editor

const loadUnsavedData = () => {
  editor?.setData(unsavedData.value)
}

function getData() {
  if (editor) {
    return editor.getData()
  }
  return null
}

// const forceToUpdateModel = () => {
//   emit('update:modelValue', editor.getData())
// }

let currentData = '';

onMounted(() => {
  unsavedData.value = localStorage.getItem(props.formRole)
  ClassicEditor
    .create(editorRef.value, {
      licenseKey: '',
      placeholder: props.placeholder,
      removePlugins: [],
      simpleUpload: {
        uploadUrl: route('upload-adapter'),
        withCredentials: true,
        headers: {
          'X-CSRF-TOKEN': usePage().props.value.csrf_token,
          Accept: 'application/json',
        }
      },
      autosave: {
        save(editor) {
          localStorage.setItem(props.formRole, editor.getData())
        }
      },
    })
    .then(e => {
      editor = e
      editor.model.document.on('change', evt => {
        currentData = editor.getData()
        emit('update:modelValue', currentData)
      });
      editor.setData(props.modelValue)
    })
})

defineExpose({
  // forceToUpdateModel
})
</script>

<style>
:root {
  --ck-border-radius: 0.25rem;
  --ck-color-base-border: rgb(55 65 81);
}

:root .dark {
  --ck-color-text: white;
  --ck-color-toolbar-background: rgb(31 41 55);
  /*--ck-color-base-background: rgb(31 41 55);*/
}

.dark .ck-editor tbody tr:nth-child(odd) {
  background-color: rgb(249 250 251 / var(--tw-bg-opacity)) ;
}

</style>
