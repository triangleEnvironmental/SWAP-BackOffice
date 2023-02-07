<template>
  <Head title="Edit About Page"/>

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleBarSub
        :icon="mdiBallotOutline"
        main
        title="Edit About Page"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="mdiBallot"
        form
        @submit.prevent="submit"
      >

        <FormField
          label="Page content in English"
          required
          :icon="Icons.content"
          :error="form.errors.content_en"
        >
          <Wysiwyg
            v-model="form.content_en"
            form-role="ABOUT_CONTENT_EN"
            placeholder="Write page content in English"
          />
        </FormField>

        <FormField
          label="Page content in Khmer"
          :icon="Icons.content"
          :error="form.errors.content_km"
        >
          <Wysiwyg
            v-model="form.content_km"
            form-role="ABOUT_CONTENT_KM"
            placeholder="Write page content in Khmer"
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
  'about',
])

const form = useForm({
  content_en: '',
  content_km: '',
})

const resetEditForm = () => {
  form.content_en = props.about.content_en ?? ''
  form.content_km = props.about.content_km ?? ''
}

onMounted(() => {
  resetEditForm()
})

const resetForm = () => {
    resetEditForm()
}

const submit = () => {
  form.transform(data => ({
    ...data,
    _method: 'PUT'
  })).post(route('page.update', {page_key: 'about'}), {
    preserveScroll: false,
    onSuccess: () => {
    },
  })
}

</script>
