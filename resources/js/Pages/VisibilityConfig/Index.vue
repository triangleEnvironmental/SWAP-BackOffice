<template>
  <Head title="Report Visibility Configuration"/>

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleBarSub
        :icon="mdiDatabaseEyeOutline"
        main
        title="Report Visibility Configuration"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="mdiCogOutline"
        form
        @submit.prevent="submit"
      >

        <FormField label="Duration range for reports to be visible (days)"
                   required
                   :icon="mdiTableHeadersEye"
                   :error="configForm.errors.duration_for_visible">

          <FormControl
            v-model="configForm.duration_for_visible[0]"
            type="number"
            :icon="mdiCalendarRange"
            placeholder="From number of days"
          />

          <FormControl
            v-model="configForm.duration_for_visible[1]"
            type="number"
            :icon="mdiCalendarRange"
            placeholder="To number of days"
          />

        </FormField>

        <FormField label="Number of days to display private reports when they are ignored"
                   required
                   :icon="mdiTimerOutline"
                   :error="configForm.errors.duration_ignored_private">

          <FormControl
            v-model="configForm.duration_ignored_private"
            type="number"
            :icon="mdiCalendarRange"
            placeholder="Number of days"
          />

        </FormField>

        <BaseDivider/>

        <BaseButtons>
          <BaseButton
            type="submit"
            color="info"
            label="Submit"
          />
          <BaseButton
            color="info"
            outline
            label="Reset"
            @click="resetForm"
          />
        </BaseButtons>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script setup>
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import PageNotification from '@/Components/PageNotification.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import {
  mdiCogOutline,
  mdiDatabaseEyeOutline,
  mdiTimerOutline,
  mdiTableHeadersEye,
  mdiTagMultiple,
  mdiCalendarRange
} from "@mdi/js";
import {useForm} from "@inertiajs/inertia-vue3";

const props = defineProps([
  'duration_for_visible',
  'duration_ignored_private',
])

const configForm = useForm({
  duration_for_visible: props.duration_for_visible,
  duration_ignored_private: props.duration_ignored_private,
})

const resetForm = () => {
  configForm.reset()
}

const submit = () => {
  configForm.transform(data => ({
    ...data,
    _method: 'PUT',
  })).post(route('visibility-config.update'), {
    preserveScroll: false,
    onSuccess: () => {},
  })
}

</script>

<style scoped>

</style>
