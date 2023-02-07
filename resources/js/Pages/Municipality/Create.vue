<template>
  <Head :title="title"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>

    <SectionMain>
      <SectionTitleBarSub
        :icon="mdiBallotOutline"
        main
        :title="title"
      >
        <template v-slot:actions>
          <BaseButton
            v-if="data && data.can_view_area"
            color="info"
            :route-name="route('municipality-area.list', {id: data.id})"
            :icon="Icons.area"
            label="Manage Service Area"
          />
        </template>
      </SectionTitleBarSub>

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="mdiBallot"
        form
        @submit.prevent="submit"
      >
        <FormField label="Logo"
                   :icon="mdiFileImageOutline"
                   :error="spForm.errors.logo">

          <!-- Current Profile Photo -->
          <div v-if="!logoPreview && data && data.logo_url" class="mt-2">
            <photo-provider>
              <photo-consumer :src="data.logo_url">
                <img :src="data.logo_url" :alt="data.logo_url" class="h-40">
              </photo-consumer>
            </photo-provider>
          </div>

          <!-- New Profile Photo Preview -->
          <div v-show="logoPreview" class="mt-2">
            <img class="h-40" :src="logoPreview" alt="Logo preview">
          </div>

          <BaseButtons v-if="allowEdit">
            <FormFilePicker ref="uploadForm" v-model="logoInput" label="Select A New Logo"/>
            <BaseButton
              v-if="data && data.logo_path"
              type="button"
              @click.prevent="deleteLogo"
              label="Remove Logo"
            />
          </BaseButtons>
        </FormField>

        <FormField
          label="Name"
          required
          :icon="mdiFormTextbox"
          :error="spForm.errors.name_en"
        >
          <FormControl
            v-model="spForm.name_en"
            type="text"
            :readonly="!allowEdit"
            placeholder="Name in English"
          />

          <FormControl
            v-model="spForm.name_km"
            type="text"
            :readonly="!allowEdit"
            placeholder="Name in Khmer"
          />
        </FormField>

        <FormField
          label="Description"
          :icon="mdiImageText"
          :error="spForm.errors.description_en"
        >
          <FormControl
            v-model="spForm.description_en"
            type="textarea"
            :readonly="!allowEdit"
            placeholder="Description in English"
          />

          <FormControl
            v-model="spForm.description_km"
            type="textarea"
            :readonly="!allowEdit"
            placeholder="Description in Khmer"
          />
        </FormField>

        <FormField
          label="Sectors"
          :icon="mdiHomeGroup"
          :error="spForm.errors.sector_ids"
        >
          <SelectForm v-model="spForm.sector_ids"
                      multiple
                      :readonly="!allowEdit"
                      label="name_en"
                      :reduce="category => category.id"
                      :options="sector_options ?? []"/>

        </FormField>

        <template v-if="allowEdit">
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
        </template>
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
  'sector_options',
  'data',
])

const allowEdit = computed(() => {
  if (props.data == null) {
    return true
  }
  return props.data.can_update
})

const uploadForm = ref(null)

const logoInput = ref(null);
const logoPreview = ref(null);
const confirmModalRef = ref(null)

watch(logoInput, async (newValue) => {
  if (!newValue) {
    logoPreview.value = null
  } else {
    logoPreview.value = await uploadForm.value.getPreview();
  }
})

const title = computed(() => {
  if (!isEdit.value) {
    return 'New Municipality'
  } else {
    if (route().current('my-municipality.edit')) {
      return props.data.name_en
    }
    return 'Edit Municipality'
  }
})

const deleteLogo = async () => {
  const confirm = await confirmModalRef.value.openModal({
    title: 'Delete municipality logo',
    content: 'Please confirm that you need to delete the current municipality logo.'
  });
  if (confirm) {
    Inertia.delete(route('municipality-logo.delete', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
        clearLogoFileInput();
      },
    });
  }
}

const resetEditForm = () => {
  spForm.name_en = props.data.name_en
  spForm.name_km = props.data.name_km
  spForm.logo = null
  spForm.description_en = props.data.description_en
  spForm.description_km = props.data.description_km
  spForm.sector_ids = props.data.sectors?.map(e => e.id) ?? []
  clearLogoFileInput()
}

const resetForm = () => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    spForm.reset()
    clearLogoFileInput()
  }
}

const clearLogoFileInput = () => {
  if (logoInput.value) {
    logoInput.value = null;
  }
};

const isEdit = computed(() => {
  return !route().current('municipality.create')
})

const spForm = useForm({
  name_en: '',
  name_km: '',
  logo: null,
  description_en: '',
  description_km: '',
  sector_ids: [],
})

const submit = () => {
  if (logoInput.value) {
    spForm.logo = logoInput.value;
  }

  if (isEdit.value) {
    spForm.transform(data => ({
      ...data,
      _method: 'PUT'
    })).post(route('municipality.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    });
  } else {
    spForm.post(route('municipality.post'), {
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
