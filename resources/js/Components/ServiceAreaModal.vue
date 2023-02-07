<template>
  <CardBoxModal
    v-model="computedValue"
    ref="modalRef"
    :title="data ? data.name_en : 'New Service Area'"
    button-label="Submit"
    @confirm="onSubmit(form)"
    :close-on-done="false"
    :has-cancel="true"
  >
    <!--    <FormValidationErrors/>-->
    <PageNotification inline/>

    <FormField
      label="Area name"
      :icon="Icons.name"
      :error="form.errors.name_en"
    >
      <FormControl
        v-model="form.name_en"
        type="input"
        placeholder="Area name in English"
      />

      <FormControl
        v-model="form.name_km"
        type="input"
        placeholder="Area name in Khmer"
      />
    </FormField>

    <FormField
      label="KML File"
      :icon="Icons.file"
      :error="form.errors.kml"
    >
      <FormFilePicker v-model="form.kml"
                      :label="isEdit ? 'Upload another' : 'Upload KML'"
                      accept=".kml"/>
    </FormField>
  </CardBoxModal>
</template>

<script setup>
import CardBoxModal from "@/Components/CardBoxModal.vue"
import BaseButton from "@/Components/BaseButton.vue"
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import {ref, computed, watch} from "vue";
import {useForm, usePage} from "@inertiajs/inertia-vue3";

const emit = defineEmits([
  'update:modelValue'
])

const modalRef = ref()

const form = useForm({
  name_en: null,
  name_km: null,
  kml: null
})

const isEdit = computed(() => props.data != null)

const props = defineProps({
  modelValue: [Boolean, String, null, Object],
  data: Object,
  institutionType: {
    type: String,
    required: true,
  }
})

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    usePage().props.value.jetstream.flash = {}
    usePage().props.value.errors = {}
    form.errors = {}
    updateForm()
  }
})

const updateForm = () => {
  if (props.data) {
    form.name_en = props.data.name_en
    form.name_km = props.data.name_km
  } else {
    form.name_en = null
    form.name_km = null
  }
  form.kml = null
}

const onSubmit = (form) => {
  if (isEdit.value) {
    form.transform(data => ({
      ...data,
      _method: 'PUT'
    })).post(
      route(`${props.institutionType}-area.update`, {
        id: route().params.id,
        area_id: props.data.id
      }),
      {
        preserveScroll: true,
        onSuccess: () => {
          modalRef.value.close()
        },
      }
    )
  } else {
    form.transform(data => ({
      ...data,
      _method: 'POST'
    })).post(
      route(`${props.institutionType}-area.create`, {id: route().params.id}),
      {
        preserveScroll: true,
        onSuccess: () => {
          modalRef.value.close()
        },
      }
    )
  }
}

const computedValue = computed({
  get: () => props.modelValue,
  set: value => {
    emit('update:modelValue', value)
  }
})
</script>

<style scoped>

</style>
