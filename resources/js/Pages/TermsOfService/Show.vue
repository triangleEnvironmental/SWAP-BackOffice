<script setup>
import {usePage} from '@inertiajs/inertia-vue3';
import LayoutGuest from '@/Layouts/LayoutGuest.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import {computed, onMounted, ref} from "vue";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const titleStack = ref(['Backoffice', 'Terms of Services'])

defineProps({
  terms: String,
  can_update: Boolean,
});

const isLoggedIn = computed(() => {
  return !!usePage().props.value.user
})

// const iframe = ref()
//
// onMounted(() => {
//   iframe.value.onload = function() {
//     iframe.value.style.height =
//       iframe.value.contentWindow.document.body.scrollHeight + 'px';
//   }
// })

</script>

<template>
  <Head title="Terms of Service"/>

  <component :is="isLoggedIn ? LayoutAuthenticated : LayoutGuest">
    <SectionTitleBar v-if="isLoggedIn" :title-stack="titleStack">
      <template v-slot:action>
<!--        <BaseButtons>-->
<!--          <BaseButton v-if="can_update"-->
<!--                      label="Edit"-->
<!--                      :icon="Icons.edit"-->
<!--                      color="info"-->
<!--                      outline-->
<!--                      route-name="terms.edit"/>-->
<!--        </BaseButtons>-->
      </template>
    </SectionTitleBar>

    <SectionMain>
      <CardBox class="mb-6">
                <div class="w-full prose max-w-full"
                     v-html="terms"/>
<!--        <div name="termly-embed" data-id="77013405-bb2d-4d93-ac95-1c592cb1b731" data-type="iframe">-->
<!--          <div>-->
<!--            <div class="Iframe-content-97a9de">-->
<!--              <iframe ref="iframe"-->
<!--                      allowfullscreen=""-->
<!--                      class="Iframe-iframe-ad8182 w-full"-->
<!--                      frameborder="0"-->
<!--                      id="77013405-bb2d-4d93-ac95-1c592cb1b731"-->
<!--                      scrolling="no"-->
<!--                      src="https://app.termly.io/embed/terms-of-use/77013405-bb2d-4d93-ac95-1c592cb1b731">-->
<!--                <p>-->
<!--                  Your browser does not support iframes.-->
<!--                </p>-->
<!--              </iframe>-->
<!--            </div>-->
<!--          </div>-->
<!--        </div>-->
      </CardBox>
    </SectionMain>
  </component>
</template>
