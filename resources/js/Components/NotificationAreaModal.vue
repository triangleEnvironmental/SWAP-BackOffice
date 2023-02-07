<template>
  <CardBoxModal
    v-model="computedValue"
    ref="modalRef"
    :icon="Icons.notification"
    :title="`Send notification to ${data.name_en}`"
    button-label="Submit"
    @confirm="onSubmit(form)"
    :close-on-done="false"
    :has-cancel="true"
    :show-setting-button="true"
    :header-icon="Icons.preset"
    :on-click-setting="pickPreset"
    :setting-tooltip="'Select preset'"
  >
    <!--    <FormValidationErrors/>-->

    <FormField
      label="Title"
      :icon="Icons.name"
      :error="form.errors.title"
    >
      <FormControl
        v-model="form.title"
        type="input"
        placeholder="Notification title"
      />
    </FormField>

    <FormField
      label="Description"
      :icon="Icons.name"
      :error="form.errors.description"
    >
      <FormControl
        v-model="form.description"
        type="input"
        placeholder="Notification body"
      />
    </FormField>
  </CardBoxModal>

  <NotificationPresetModal v-model="notificationPresetModalOpen"
                           v-if="notificationPresetModalOpen"
                           @selected="onPresetOptionSelected"
                           :area-id="data.id"/>
</template>

<script setup>
import CardBoxModal from "@/Components/CardBoxModal.vue"
import BaseButton from "@/Components/BaseButton.vue"
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import {ref, computed, watch} from "vue";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import NotificationPresetModal from "@/Components/NotificationPresetModal.vue"

const emit = defineEmits([
  'update:modelValue'
])

const modalRef = ref()
const notificationPresetModalOpen = ref(false)

const form = useForm({
  title: null,
  description: null,
})

const isEdit = computed(() => props.data != null)

const props = defineProps({
  modelValue: [Boolean, String, null, Object],
  data: Object,
})

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    usePage().props.value.jetstream.flash = {}
    usePage().props.value.errors = {}
    form.errors = {}
    updateForm()
  }
})

const pickPreset = () => {
  notificationPresetModalOpen.value = true
}

const updateForm = () => {
  form.title = null
  form.description = null
}

const onPresetOptionSelected = (preset) => {
  form.title = preset.title
  form.description = preset.description
}

const onSubmit = (form) => {
  form.transform(data => ({
    ...data,
    _method: 'POST'
  })).post(
    route(`notification-to-area.create`, {
      area_id: props.data.id,
    }),
    {
      preserveScroll: true,
      onSuccess: () => {
        modalRef.value.close()
      },
    }
  )
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
