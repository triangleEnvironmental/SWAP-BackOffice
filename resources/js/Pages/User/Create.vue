<template>
  <Head :title="isEdit ? 'Edit User' : 'New User'"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleBarSub
        :icon="mdiBallotOutline"
        main
        :title="isEdit ? 'Edit User' : 'New User'"
      />

      <PageNotification/>

      <CardBox
        title="Forms"
        :icon="mdiBallot"
        form
        @submit.prevent="submit"
      >

        <FormField label="Profile Photo"
                   :icon="mdiFileImageOutline"
                   :error="userForm.errors.profile_photo">

          <!-- Current Profile Photo -->
          <div v-if="!photoPreview && data && data.profile_photo_url" class="mt-2">
            <photo-provider>
              <photo-consumer :src="data.profile_photo_url">
                <img :src="data.profile_photo_url" :alt="data.profile_photo_url" class="h-40">
              </photo-consumer>
            </photo-provider>
          </div>

          <!-- New Profile Photo Preview -->
          <div v-else-if="photoPreview" class="mt-2">
            <img class="h-40" :src="photoPreview" alt="Profile photo preview">
          </div>

          <BaseButtons>
            <FormFilePicker ref="uploadForm" v-model="photoInput" label="Select A New Profile Photo"/>
            <BaseButton
              v-if="data && data.profile_photo_path"
              type="button"
              @click.prevent="deletePhoto"
              label="Remove Logo"
            />
          </BaseButtons>
        </FormField>

        <FormField
          label="Name"
          required
          :icon="mdiAccountDetailsOutline"
          :error="userForm.errors.name"
        >
          <FormControl
            v-model="userForm.name"
            type="text"
            placeholder="Full Name"
          />
        </FormField>

        <FormField
          label="E-mail"
          required
          :icon="mdiEmailOutline"
          :error="userForm.errors.email"
        >
          <FormControl
            v-model="userForm.email"
            autocomplete="username"
            type="email"
            placeholder="E-mail"
          />
        </FormField>

        <FormField
          label="Password"
          :required="!isEdit"
          :icon="mdiFormTextbox"
          :help="isEdit ? 'Leave empty if no need to update' : null"
          :error="userForm.errors.password"
        >
          <FormControl
            v-model="userForm.password"
            type="password"
            autocomplete="new-password"
            placeholder="Password"
          />

          <FormControl
            v-model="userForm.password_confirmation"
            type="password"
            autocomplete="new-password"
            placeholder="Confirm Password"
          />
        </FormField>

        <FormField
          label="Role"
          required
          :icon="mdiPoliceBadgeOutline"
          :error="userForm.errors.role_id"
        >
          <SelectForm v-model="userForm.role_id"
                      label="name_en"
                      placeholder="Select a role ..."
                      :reduce="role => role.id"
                      :options="role_options ?? []"/>
        </FormField>

        <template v-if="isNeedInstitution">

          <FormField
            label="Type of Institution"
            required
            :icon="mdiTagOutline"
          >
            <FormCheckRadioPicker
              v-model="institutionType"
              name="institution_type"
              type="radio"
              :options="{ service_provider: 'Service Provider', municipality: 'Municipality' }"
            />
          </FormField>

          <FormField
            v-if="institutionType === 'service_provider'"
            label="Service Provider"
            required
            :icon="mdiDumpTruck"
            :error="userForm.errors.institution_id"
          >
            <SelectForm v-model="userForm.institution_id"
                        label="name_en"
                        placeholder="Select a service provider ..."
                        :reduce="institution => institution.id"
                        :options="service_provider_options ?? []"/>
          </FormField>

          <FormField
            v-else-if="institutionType === 'municipality'"
            label="Municipality"
            required
            :icon="mdiBank"
            :error="userForm.errors.institution_id"
          >
            <SelectForm v-model="userForm.institution_id"
                        label="name_en"
                        placeholder="Select a municipality ..."
                        :reduce="institution => institution.id"
                        :options="municipality_options ?? []"/>
          </FormField>

        </template>

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
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {
  mdiTagOutline,
  mdiDumpTruck,
  mdiPoliceBadgeOutline,
  mdiAccountDetailsOutline,
  mdiEmailOutline,
  mdiFormTextbox,
  mdiBallotOutline,
  mdiBallot,
  mdiHomeGroup,
  mdiFileImageOutline,
  mdiBank
} from "@mdi/js";
import {computed, onMounted, ref, watch} from 'vue'
import SelectForm from "@/Components/SelectForm.vue";
import {Inertia} from "@inertiajs/inertia";
import ConfirmModal from "@/Components/ConfirmModal.vue"

const props = defineProps([
  'service_provider_options',
  'municipality_options',
  'role_options',
  'preset_institution_id',
  'data',
])

const uploadForm = ref(null)

const photoInput = ref(null);
const photoPreview = ref(null);
const confirmModalRef = ref(null)

const isEdit = computed(() => {
  return route().current('user.edit')
})

const getDefaultInstitutionType = () => {
  if (isEdit.value) {
    if (props.data.institution) {
      return props.data.institution.is_municipality ? 'municipality' : 'service_provider'
    }
  }
  return 'service_provider'
}

const institutionType = ref(getDefaultInstitutionType())

watch(institutionType, (newValue) => {
  if (isEdit.value) {
    if (newValue === getDefaultInstitutionType()) {
      userForm.institution_id = props.data.institution_id
      return
    }
  }
  userForm.institution_id = null
})

const isNeedInstitution = computed(() => {
  if (props.preset_institution_id) {
    return false
  }

  if (userForm.role_id) {
    try {
      return props.role_options.filter(e => e.id === userForm.role_id)[0].is_under_institution
    } catch (e) {
      return false;
    }
  }
  return false;
})

watch(photoInput, async (newValue) => {
  if (!newValue) {
    photoPreview.value = null
  } else {
    photoPreview.value = await uploadForm.value.getPreview();
  }
})

const deletePhoto = async () => {
  const confirm = await confirmModalRef.value.openModal({
    title: 'Delete user profile photo',
    content: 'Please confirm that you need to delete the current user profile photo.'
  });
  if (confirm) {
    Inertia.delete(route('user-photo.delete', {id: route().params.id}), {
      preserveScroll: true,
      onSuccess: () => {
        clearPhotoFileInput();
      },
    });
  }
}

const resetEditForm = () => {
  userForm.name = props.data.name
  userForm.email = props.data.email
  userForm.password = null
  userForm.password_confirmation = null
  userForm.role_id = props.data.role_id
  userForm.profile_photo = null
  userForm.institution_id = props.data.institution_id
  institutionType.value = getDefaultInstitutionType()
  clearPhotoFileInput()
}

const resetForm = () => {
  if (isEdit.value) {
    resetEditForm()
  } else {
    userForm.reset()
    clearPhotoFileInput()
  }
}

const clearPhotoFileInput = () => {
  if (photoInput.value) {
    photoInput.value = null;
  }
};

const userForm = useForm({
  name: '',
  email: '',
  password: null,
  password_confirmation: null,
  role_id: null,
  institution_id: props.preset_institution_id,
  profile_photo: null,
})

const submit = () => {
  if (photoInput.value) {
    userForm.profile_photo = photoInput.value;
  }

  if (isEdit.value) {
    userForm.transform(data => ({
      ...data,
      email: data.email,
      _method: 'PUT'
    })).post(route('user.update', {id: route().params.id}), {
      preserveScroll: false,
      onSuccess: () => {
      },
    });
  } else {
    userForm.transform(data => ({
      ...data,
      email: data.email,
    })).post(route('user.post'), {
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
