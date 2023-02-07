<template>
  <Head title="New Sector"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>

    <SectionMain>
      <SectionTitleBarSub
        :icon="Icons.formOutline"
        main
        :title="isEdit ? 'Edit Sector' : 'New Sector'"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="Icons.form"
        form
        @submit.prevent="submit"
      >

        <FormField label="Icon"
                   :icon="mdiFileImageOutline"
                   :error="form.errors.icon">

          <!-- Current Profile Photo -->
          <div v-if="!iconPreview && data && data.icon_url" class="mt-2">
            <photo-provider>
              <photo-consumer :src="data.icon_url">
                <img :src="data.icon_url" :alt="data.icon_url" class="h-40">
              </photo-consumer>
            </photo-provider>
          </div>

          <!-- New Profile Photo Preview -->
          <div v-show="iconPreview" class="mt-2">
            <img class="h-40" :src="iconPreview" alt="Icon preview">
          </div>

          <BaseButtons>
            <FormFilePicker ref="uploadForm" v-model="iconInput" label="Select A New Icon"/>
            <BaseButton
              v-if="data && data.icon_path"
              type="button"
              @click.prevent="deleteIcon"
              label="Remove Icon"
            />
          </BaseButtons>
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
  return route().current('sector.edit')
})

const uploadForm = ref(null)

const iconInput = ref(null);
const iconPreview = ref(null);
const confirmModalRef = ref(null)

const clearIconFileInput = () => {
  if (iconInput.value) {
    iconInput.value = null;
  }
};

const deleteIcon = async () => {
  const confirm = await confirmModalRef.value.openModal({
    title: 'Delete sector icon',
    content: 'Please confirm that you need to delete this sector icon.'
  });
  if (confirm) {
    Inertia.delete(route('sector-icon.delete', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
        clearIconFileInput();
      },
    });
  }
}

const resetEditForm = () => {
  form.name_en = props.data.name_en
  form.name_km = props.data.name_km
  form.icon = null
  clearIconFileInput()
}

const resetForm = () => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    form.reset()
    clearIconFileInput()
  }
}

const form = useForm({
  name_en: '',
  name_km: '',
  icon: null,
})

const submit = () => {
  if (iconInput.value) {
    form.icon = iconInput.value;
  }

  if (isEdit.value) {
    form.transform(data => ({
      ...data,
      _method: 'PUT'
    })).post(route('sector.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    });
  } else {
    form.post(route('sector.post'), {
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
