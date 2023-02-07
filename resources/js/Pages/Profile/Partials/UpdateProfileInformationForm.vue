<script setup>
import {computed, ref, watch} from 'vue';
import {Inertia} from '@inertiajs/inertia';
import {Link, useForm} from '@inertiajs/inertia-vue3';
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import CardBox from '@/Components/CardBox.vue';
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import FormFilePicker from '@/Components/FormFilePicker.vue';
import {mdiAccount, mdiMail} from '@mdi/js'

const props = defineProps({
  user: Object,
});

const uploadForm = ref(null)

const form = useForm({
  _method: 'PUT',
  name: props.user.name,
  email: props.user.email,
  photo: null,
});

const verificationLinkSent = ref(null);
const photoInput = ref(null);
const photoPreview = ref(null);

watch(photoInput, async () => {
  photoPreview.value = await uploadForm.value.getPreview();
})

const updateProfileInformation = () => {
  if (photoInput.value) {
    form.photo = photoInput.value;
  }

  form.transform(data => ({
    ...data,
    email: data.email,
  })).post(route('user-profile-information.update'), {
    errorBag: 'updateProfileInformation',
    preserveScroll: true,
    onSuccess: () => clearPhotoFileInput(),
  });
};

const sendEmailVerification = () => {
  verificationLinkSent.value = true;
};

const deletePhoto = () => {
  Inertia.delete(route('current-user-photo.destroy'), {
    preserveScroll: true,
    onSuccess: () => {
      photoPreview.value = null;
      clearPhotoFileInput();
    },
  });
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};
</script>

<template>
  <div>
    <form @submit.prevent="updateProfileInformation">

      <CardBox title="Profile Information">
        <!-- Profile Photo -->
        <FormField v-if="$page.props.jetstream.managesProfilePhotos"
                   label="Photo"
                   :error="form.errors.photo">

          <!-- Current Profile Photo -->
          <div v-show="!photoPreview" class="mt-2">
            <photo-provider>
              <photo-consumer :src="user.profile_photo_url">
                <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full h-20 w-20 object-cover">
              </photo-consumer>
            </photo-provider>
          </div>

          <!-- New Profile Photo Preview -->
          <div v-show="photoPreview" class="mt-2">
                    <span
                      class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                      :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
          </div>

          <BaseButtons>
            <FormFilePicker ref="uploadForm" v-model="photoInput" label="Select A New Photo"/>
            <BaseButton
              v-if="user.profile_photo_path"
              type="button"
              @click.prevent="deletePhoto"
              label="Remove Photo"
            />
          </BaseButtons>
        </FormField>

        <!-- Name -->
        <FormField label="Name"
                   :error="form.errors.name">
          <FormControl
            id="name"
            :icon="mdiAccount"
            v-model="form.name"
            type="text"
            autocomplete="name"
          />
        </FormField>

        <!-- Email -->
        <FormField label="Email"
                   :error="form.errors.email">
          <FormControl
            id="email"
            :icon="mdiMail"
            v-model="form.email"
            type="email"
            autocomplete="name"
          />
        </FormField>

        <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
          <p class="text-sm mt-2">
            Your email address is unverified.

            <Link
              :href="route('verification.send')"
              method="post"
              as="button"
              class="underline text-gray-600 hover:text-gray-900"
              @click.prevent="sendEmailVerification"
            >
              Click here to re-send the verification email.
            </Link>
          </p>

          <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
            A new verification link has been sent to your email address.
          </div>
        </div>

        <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
          Saved.
        </JetActionMessage>

        <BaseButtons>
          <BaseButton :class="{ 'opacity-25': form.processing }"
                      type="submit"
                      :disabled="form.processing"
                      label="Save"
                      color="info"/>
        </BaseButtons>
      </CardBox>
    </form>
  </div>
</template>
