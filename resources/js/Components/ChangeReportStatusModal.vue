<template>
  <CardBoxModal
    v-model="value"
    :title="'Change report status'"
    button-label="Submit"
    :has-button="true"
    :close-on-done="false"
    @confirm="onConfirm"
    :small="false"
    :has-cancel="true">

    <div class="grid grid-cols-12 gap-6">
      <div class="col-span-12 lg:col-span-6 xl:col-span-5 order-1 lg:order-2">
        <FormField label="Report status"
                   :error="form.errors.status_id">
          <FormCheckRadioPicker
            v-model="form.status_id"
            class="ml-2 my-3"
            name="Report Status"
            type="radio"
            column
            :options="statusOptions"
          >
            <template v-slot="data">
              <div class="flex justify-between">
          <span class="font-bold ml-2" :style="`color: ${data.option.color}`">
            {{ data.option.name_en }}
          </span>
                <span v-if="data.option.id == props.report.report_status_id"
                      class="italic text-gray-400">
            ( Current status )
          </span>
              </div>
            </template>
          </FormCheckRadioPicker>
        </FormField>
      </div>
      <div v-if="report.reported_by_user_id" class="col-span-12 lg:col-span-6 xl:col-span-7 order-2 lg:order-1">
        <h4 class="font-bold text-xl mb-4 flex justify-between">
          <div>
            <BaseIcon size="18" :path="Icons.notificationSetting"/> Notification
          </div>
          <BaseButton v-tooltip="'Select a preset'" :icon="Icons.preset" color="info" outline @click="pickPreset"/>
        </h4>
        <FormField label="Title"
                   :error="form.errors.notification_title">
          <FormControl v-model="form.notification_title"
                       :placeholder="notificationTitlePlaceholder"/>
        </FormField>

        <FormField label="Body"
                   :error="form.errors.notification_body">
          <FormControl v-model="form.notification_body"
                       :placeholder="notificationBodyPlaceholder"/>
        </FormField>
      </div>
    </div>

  </CardBoxModal>

  <NotificationPresetModal v-model="notificationPresetModalOpen"
                           v-if="notificationPresetModalOpen"
                           @selected="onPresetOptionSelected"/>
</template>

<script setup>
import CardBoxModal from '@/Components/CardBoxModal.vue'
import NotificationPresetModal from '@/Components/NotificationPresetModal.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormCheckRadioPicker from '@/Components/FormCheckRadioPicker.vue'
import {computed, ref, watch} from "vue";
import useHelper from "@/Composibles/useHelper";
import {useForm} from "@inertiajs/inertia-vue3";
import BaseButton from "@/Components/BaseButton.vue";

const props = defineProps({
  report: {
    type: Object,
    required: true,
  },
  options: {
    type: Array,
    required: true
  },
  modelValue: {
    type: Boolean,
  }
})

const notificationPresetModalOpen = ref(false)

const onPresetOptionSelected = (preset) => {
  form.notification_title = preset.title
  form.notification_body = preset.description
}

const form = useForm({
  status_id: props.report.report_status_id,
  notification_title: '',
  notification_body: '',
})

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    form.status_id = props.report.report_status_id
    form.notification_title = ''
    form.notification_body = ''
  }
})

const notificationTitlePlaceholder = computed(() => 'Report has been updated')

const pickPreset = () => {
  notificationPresetModalOpen.value = true
}

const notificationBodyPlaceholder = computed(() => {
  const find = props.options.filter(opt => {
    return opt.id == form.status_id
  })

  let status = 'updated'

  if (find.length > 0) {
    status = find[0].name_en.toLowerCase()
  }

  let time = ''
  if (props.report.created_at) {
    time = ` created ${useHelper().fromNow(props.report.created_at)}`
  }

  return `Your report${time} is now ${status}`
})

const emit = defineEmits([
  'update:modelValue',
  'submit'
])

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

const statusOptions = computed(() => {
  const options = {}
  for (const option of props.options) {
    options[option.id] = option
  }
  return options
})

const onConfirm = () => {
  emit('submit', form.transform(data => {
    if (!data.notification_title.trim()) {
      data.notification_title = notificationTitlePlaceholder.value
    }
    if (!data.notification_body.trim()) {
      data.notification_body = notificationBodyPlaceholder.value
    }
    return data
  }))
}
</script>

<style scoped>

</style>
