<script setup>
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import PaginatedTable from "@/Components/PaginatedTable.vue";
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBox from '@/Components/CardBox.vue'
import SectionMain from '@/Components/SectionMain.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import {mdiEye, mdiPlus} from "@mdi/js"
import {Inertia} from '@inertiajs/inertia'
import {ref} from 'vue'
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'citizens',
])

const titleStack = ref(['Backoffice', 'Citizens'])

</script>

<template>
  <Head title="Citizens"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
      </template>
    </SectionTitleBar>
    <SectionHeroBar>
      Citizens
      <template v-slot:action>
      </template>
    </SectionHeroBar>
    <SectionMain>
      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="citizens"
                         :checkable="false"
                         :titles="['ID', '', 'First Name', 'Last Name', 'Phone Number']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td class="border-b-0 lg:w-6 before:hidden">
              <MightNA :value="row.full_name">
                <UserAvatar
                  :username="row.full_name"
                  :avatar="row.profile_photo_url"
                  class="w-24 h-24 mx-auto lg:w-6 lg:h-6"
                />
              </MightNA>
            </td>
            <td data-label="First Name">
              <MightNA :value="row.name"/>
            </td>
            <td data-label="Last Name">
              <MightNA :value="row.last_name"/>
            </td>
            <td data-label="Phone Number">
              <MightNA :value="row.phone_number"/>
            </td>
            <td data-label="Actions">
              <BaseButtons
                type="justify-start lg:justify-end"
                no-wrap
              >
                <BaseButton
                  color="info"
                  :icon="mdiEye"
                />
              </BaseButtons>
            </td>
          </template>
        </paginated-table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>

</style>

