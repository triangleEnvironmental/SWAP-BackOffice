<template>
  <CardBoxModal
    v-model="computedValue"
    ref="modalRef"
    :icon="Icons.notification"
    :title="`Send a notification`"
    button-label="Submit"
    @confirm="onSubmit(form)"
    :close-on-done="false"
    :has-cancel="true"
  >
    <!--    <FormValidationErrors/>-->


    <template
      v-if="notificationSettings">
      <template v-if="notificationSettings.can_send_all">
        <FormField
          class="mb-0"
          label="Target"
          :icon="Icons.target"
          :error="form.errors.institution_id"
        >
          <SelectForm v-model="form.institution"
                      label="name_en"
                      placeholder="All institutions ..."
                      :reduce="institution => institution"
                      :options="notificationSettings.institutions"
                      @change="() => {form.area_ids = []}"/>
        </FormField>
      </template>
      <template v-if="form.institution">
        <FormField label="Areas"
                   :icon="Icons.area"
                   :error="form.errors.area_ids">
          <SelectForm v-model="form.area_ids"
                      label="name_en"
                      placeholder="All areas"
                      multiple
                      :reduce="area => area.id"
                      :options="form.institution.service_areas"/>
        </FormField>
      </template>
    </template>

    <FormField
      label="Title"
      required
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
      required
      :error="form.errors.description"
    >
      <FormControl
        v-model="form.description"
        type="input"
        placeholder="Notification body"
      />
    </FormField>
  </CardBoxModal>
</template>

<script setup>
import CardBoxModal from "@/Components/CardBoxModal.vue"
import BaseButton from "@/Components/BaseButton.vue"
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import {ref, computed, watch, onMounted, inject} from "vue";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import SelectForm from "@/Components/SelectForm.vue";

const emit = defineEmits([
  'update:modelValue'
])

const axios = inject('axios')

const modalRef = ref()
const notificationSettings = ref(null)

const form = useForm({
  institution: null,
  area_ids: [],
  title: null,
  description: null,
})

const props = defineProps({
  modelValue: [Boolean, String, null, Object],
  preset: [Object, null],
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
  if (notificationSettings.value != null && !notificationSettings.value.can_send_all && notificationSettings.value.institutions.length > 0) {
    form.institution = notificationSettings.value.institutions[0]
  } else {
    form.institution = null
    form.area_ids = []
  }
  form.title = props.preset?.title
  form.description = props.preset?.description
}

const onSubmit = (form) => {
  form.transform(data => {
    console.log('Transform', data.institution?.id)
    return {
      ...data,
      'institution_id': data.institution?.id,
      _method: 'POST'
    }
  }).post(
    route(`notification.create`),
    {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // modalRef.value.close()
        computedValue.value = false
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

const updateOptions = async () => {
  const response = await axios.get(route('notification.form'), {
    params: {}
  })

  notificationSettings.value = response.data.data
  updateForm()
}

onMounted(() => {
  updateForm()
  updateOptions()
})
</script>

<style scoped>

</style>
