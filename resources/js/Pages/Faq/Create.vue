<template>
  <Head :title="isEdit ? 'Edit FAQ' : 'New FAQ'"/>

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleBarSub
        :icon="mdiBallotOutline"
        main
        :title="isEdit ? 'Edit Clean City Tips' : 'New Clean City Tips'"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="mdiBallot"
        form
        @submit.prevent="submit"
      >

        <FormField label="Sector"
                   required
                   :icon="mdiHomeGroup"
                   :error="faqForm.errors.sector_id">
          <SelectForm v-model="faqForm.sector_id"
                      label="name_en"
                      placeholder="Select a sector ..."
                      :reduce="sector => sector.id"
                      :options="sector_options ?? []"/>
<!--          -->
<!--          <FormControl-->
<!--            v-model="faqForm.sector_id"-->
<!--            :class="{-->
<!--              'dark:text-gray-400 text-gray-600': !faqForm.sector_id,-->
<!--            }"-->
<!--            :options="sectorSelectOptions"-->
<!--            placeholder="Select a sector ..."-->
<!--          />-->
        </FormField>

        <FormField
          label="Question"
          required
          :icon="mdiChatQuestion"
          :error="faqForm.errors.question_en"
        >
          <FormControl
            v-model="faqForm.question_en"
            type="text"
            placeholder="Question in English"
          />

          <FormControl
            v-model="faqForm.question_km"
            type="text"
            placeholder="Question in Khmer"
          />
        </FormField>

        <FormField
          label="Answer to make city clean"
          required
          :icon="mdiForum"
          :error="faqForm.errors.answer_en"
        >
          <Wysiwyg
            v-model="faqForm.answer_en"
            form-role="FAQ_EN"
            placeholder="Answer in English"
          />

          <Wysiwyg
            v-model="faqForm.answer_km"
            form-role="FAQ_KM"
            placeholder="Answer in Khmer"
          />
        </FormField>

        <FormField
          label="FAQ Categories"
          :icon="mdiTagMultiple"
          :error="faqForm.errors.faq_category_ids"
        >
          <SelectForm v-model="faqForm.faq_category_ids"
                      multiple
                      label="name_en"
                      :reduce="category => category.id"
                      :options="faq_category_options ?? []"/>

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
import Wysiwyg from "@/Components/Wysiwyg.vue";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {mdiChatQuestion, mdiBallotOutline, mdiBallot, mdiHomeGroup, mdiTagMultiple, mdiForum} from "@mdi/js";
import {computed, onMounted, ref} from 'vue'
import SelectForm from "@/Components/SelectForm.vue";

const props = defineProps([
  'sector_options',
  'faq_category_options',
  'data',
])

const isEdit = computed(() => {
  return route().current('faq.edit')
})

const sectorSelectOptions = computed(() => {
  return props.sector_options.map((option) => ({
    id: option.id,
    label: option.name_en,
  }))
})

const faqForm = useForm({
  question_en: '',
  question_km: '',
  answer_en: '',
  answer_km: '',
  sector_id: null,
  faq_category_ids: [],
})

const resetEditForm = () => {
  faqForm.sector_id = props.data.sector_id
  faqForm.question_en = props.data.question_en
  faqForm.question_km = props.data.question_km
  faqForm.answer_en = props.data.answer_en
  faqForm.answer_km = props.data.answer_km
  faqForm.faq_category_ids = props.data.categories?.map(e => e.id) ?? []
}

onMounted(() => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    if (props.sector_options.length > 0) {
      faqForm.sector_id = props.sector_options[0].id
    }
  }
})

const resetForm = () => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    faqForm.reset()
  }
}

const submit = () => {
  if (isEdit.value) {
    faqForm.transform(data => ({
      ...data,
      _method: 'PUT'
    })).post(route('faq.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    })
  } else {
    faqForm.post(route('faq.post'), {
      preserveScroll: false,
      onSuccess: () => {
        resetForm()
      },
    })
  }
}

</script>

<style scoped>

</style>
