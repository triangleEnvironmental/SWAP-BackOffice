<template>
  <Head title="New FAQ Category"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleBarSub
        :icon="Icons.formOutline"
        main
        :title="isEdit ? 'Edit FAQ Category' : 'New FAQ Category'"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="Icons.form"
        form
        @submit.prevent="submit"
      >
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
  return route().current('faq-category.edit')
})

const confirmModalRef = ref(null)

const resetEditForm = () => {
  form.name_en = props.data.name_en
  form.name_km = props.data.name_km
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
})

const submit = () => {
  if (isEdit.value) {
    form.transform(data => ({
      ...data,
      _method: 'PUT',
    })).post(route('faq-category.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    });
  } else {
    form.post(route('faq-category.post'), {
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
