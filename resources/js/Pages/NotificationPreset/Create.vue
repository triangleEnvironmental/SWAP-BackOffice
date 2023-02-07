<template>
  <Head title="New Notification Preset"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>

    <SectionMain>
      <SectionTitleBarSub
        :icon="Icons.formOutline"
        main
        :title="isEdit ? 'Edit Notification Preset' : 'New Notification Preset'"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="Icons.form"
        form
        @submit.prevent="submit"
      >
        <FormField
          label="Title"
          required
          :icon="mdiFormTextbox"
          :error="form.errors.title"
        >
          <FormControl
            v-model="form.title"
            type="text"
            placeholder="Notification title"
          />
        </FormField>

        <FormField
          label="Description"
          required
          :icon="mdiFormTextbox"
          :error="form.errors.description"
        >
          <FormControl
            v-model="form.description"
            type="text"
            placeholder="Notification body"
          />
        </FormField>

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
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import CardBox from '@/Components/CardBox.vue'
import FormCheckRadioPicker from '@/Components/FormCheckRadioPicker.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import PageNotification from '@/Components/PageNotification.vue'
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {mdiImageText, mdiFormTextbox, mdiBallotOutline, mdiBallot, mdiHomeGroup, mdiFileImageOutline} from "@mdi/js";
import {computed, onMounted, ref, watch} from 'vue'
import SelectForm from "@/Components/SelectForm.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue"
import {Inertia} from "@inertiajs/inertia";

const props = defineProps([
  'data',
])

const isEdit = computed(() => {
  return route().current('notification-preset.edit')
})

const resetEditForm = () => {
  form.title = props.data.title
  form.description = props.data.description
}

const resetForm = () => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    form.reset()
  }
}

const form = useForm({
  title: '',
  description: '',
})

const submit = () => {
  if (isEdit.value) {
    form.transform(data => ({
      ...data,
      _method: 'PUT',
    })).post(route('notification-preset.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    });
  } else {
    form.post(route('notification-preset.post'), {
      preserveScroll: false,
      onSuccess: () => {
        resetForm();
      },
    });
  }
}

onMounted(() => {
  if (isEdit.value) {
    resetEditForm()
  }
})
</script>

<style scoped>

</style>
