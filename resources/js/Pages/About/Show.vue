<script setup>
import {usePage} from '@inertiajs/inertia-vue3';
import LayoutGuest from '@/Layouts/LayoutGuest.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import {computed, ref} from "vue";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

const titleStack = ref(['Backoffice', 'About'])

useRefreshNavigation();

defineProps({
  about: String,
  can_update: Boolean,
});

const isLoggedIn = computed(() => {
  return !!usePage().props.value.user
})
</script>

<template>
  <Head title="About"/>

  <component :is="isLoggedIn ? LayoutAuthenticated : LayoutGuest">
    <SectionTitleBar v-if="isLoggedIn" :title-stack="titleStack">
      <template v-slot:action>
        <BaseButtons>
          <BaseButton v-if="can_update"
                      label="Edit"
                      :icon="Icons.edit"
                      color="info"
                      outline
                      route-name="about.edit"/>
        </BaseButtons>
      </template>
    </SectionTitleBar>

    <SectionMain>
      <CardBox class="mb-6">
        <div class="w-full prose max-w-full"
             v-html="about"/>
      </CardBox>
    </SectionMain>
  </component>
</template>

