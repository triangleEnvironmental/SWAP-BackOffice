<script setup>
import {ref} from 'vue';
import {useForm} from '@inertiajs/inertia-vue3';
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import CardBox from '@/Components/CardBox.vue';
import FormFilePicker from '@/Components/FormFilePicker.vue';
import JetActionMessage from '@/Jetstream/ActionMessage.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetFormSection from '@/Jetstream/FormSection.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import {mdiAsterisk, mdiFormTextboxPassword} from '@mdi/js';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('user-password.update'), {
    errorBag: 'updatePassword',
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }

      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>

<template>
  <div>
    <form @submit.prevent="updatePassword">
      <CardBox title="Update Password">
        <input v-show="false" autocomplete="username" type="text">

        <FormField label="Current Password"
                   :error="form.errors.current_password">
          <FormControl
            id="current_password"
            :icon="mdiAsterisk"
            ref="currentPasswordInput"
            v-model="form.current_password"
            type="password"
            autocomplete="current-password"
          />
        </FormField>

        <FormField label="New Password"
                   :error="form.errors.password">
          <FormControl
            id="password"
            :icon="mdiFormTextboxPassword"
            ref="passwordInput"
            v-model="form.password"
            type="password"
            autocomplete="new-password"
          />
        </FormField>

        <FormField label="Confirm Password"
                   :error="form.errors.password_confirmation">
          <FormControl
            id="confirm-password"
            :icon="mdiFormTextboxPassword"
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
          />
        </FormField>

        <JetActionMessage :on="form.recentlySuccessful" class="mr-3">
          Saved.
        </JetActionMessage>
        <BaseButton label="Save"
                    color="info"
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
        />

      </CardBox>
    </form>
  </div>


</template>
