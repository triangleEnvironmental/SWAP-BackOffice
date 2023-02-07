<template>
  <Head title="New Report Type"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>

    <SectionMain>
      <SectionTitleBarSub
        :icon="Icons.formOutline"
        main
        :title="isEdit ? 'Edit Report Type' : 'New Report Type'"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="Icons.form"
        form
        @submit.prevent="submit"
      >
        <FormField label="Sector"
                   required
                   :icon="mdiHomeGroup"
                   :error="form.errors.sector_id">
          <SelectForm v-model="form.sector_id"
                      label="name_en"
                      placeholder="Select a sector ..."
                      :reduce="sector => sector.id"
                      :options="sector_options"/>
        </FormField>

        <FormField
          label="Name"
          required
          :icon="mdiFormTextbox"
          :error="form.errors.name_en"
        >
          <FormControl
            v-model="form.name_en"
            type="text"
            placeholder="Name in English"
          />

          <FormControl
            v-model="form.name_km"
            type="text"
            placeholder="Name in Khmer"
          />
        </FormField>

        <FormField :error="form.errors.is_private">
          <FormCheckRadioPicker v-model="form.private"
                                type="checkbox"
                                :options="[privateOptionLabel]"
                                name="is_private"/>
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
  'sector_options',
])

const isEdit = computed(() => {
  return route().current('report-type.edit')
})

const confirmModalRef = ref(null)

const privateOptionLabel = 'Is private';

const resetEditForm = () => {
  form.name_en = props.data.name_en
  form.name_km = props.data.name_km
  form.private = props.data.is_private ? [0] : []
  form.sector_id = props.data.sector_id
}

const resetForm = () => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    form.reset()
  }
}

const form = useForm({
  name_en: '',
  name_km: '',
  private: [],
  sector_id: null,
})

const submit = () => {
  if (isEdit.value) {
    form.transform(data => ({
      ...data,
      _method: 'PUT',
      is_private: data.private.length > 0,
    })).post(route('report-type.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    });
  } else {
    form.transform(data => ({
      ...data,
      is_private: data.private.length > 0,
    })).post(route('report-type.post'), {
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
