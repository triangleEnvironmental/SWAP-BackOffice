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
import {mdiEye, mdiPlus} from "@mdi/js"
import {ref} from 'vue'
import useFilterForm from "@/Composibles/useFilterForm";
import NotificationModal from '@/Components/NotificationModal.vue'
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const {filterForm, applyFilter} = useFilterForm({
  keyword: '',
  institution_id: null,
})

const props = defineProps([
  'notifications',
  'institution_options',
])

const openNotificationModal = ref(false)

const sendNotification = () => {
  openNotificationModal.value = true
}

const titleStack = ref(['Backoffice', 'Notifications'])

</script>

<template>
  <Head title="Notifications"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <NotificationModal v-model="openNotificationModal"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-notification')">
          <BaseButton label="Send a new notification"
                      :icon="Icons.notification"
                      color="warning"
                      @click="sendNotification"
          ></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar>
      Notifications
      <template v-slot:action>
        <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"
                      v-if="institution_options.length > 1"
                      v-model="filterForm.institution_id"
                      placeholder="All institutions"
                      :options="institution_options"
                      @change="applyFilter"/>
      </template>
    </SectionHeroBar>
    <SectionMain>
      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="notifications"
                         :checkable="false"
                         :titles="['ID', 'Title', 'Description', 'Target', 'Total recipients', 'Institution', 'Created on']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td data-label="Title">
              {{ row.title }}
            </td>
            <td data-label="Description">
              {{ row.description }}
            </td>
            <td data-label="Target">
              <template v-if="row.targetable_type === 'App\\Models\\Area'">
                <small class="text-gray">Area:</small><br>
                <strong>{{row.targetable.name_en}}</strong>
              </template>
              <template v-else-if="row.targetable_type === 'App\\Models\\User'">
                <small class="text-gray">Citizen:</small><br>
                <strong>{{row.targetable.full_name}}</strong>
              </template>
              <template v-else-if="row.targetable_type === 'App\\Models\\Institution'">
                <small class="text-gray">Institution:</small><br>
                <strong>{{row.targetable.name_en}}</strong>
              </template>
              <template v-else>
                <span>
                  All citizens
                </span>
<!--                <span class="text-gray-400">-->
<!--                  (N/A)-->
<!--                </span>-->
              </template>
            </td>
            <td data-label="Total recipients">
              {{ row.count_total_target_users }}
            </td>
            <td data-label="Institution">
              <MightNA :value="row.institution">
                {{ row.institution.name_en }}
              </MightNA>
            </td>
            <td data-label="Created on">
              <MightNA :value="row.created_at">
                {{$helper.formatDate(row.created_at)}}
              </MightNA>
            </td>
<!--            <td data-label="Actions">-->
<!--              <BaseButtons-->
<!--                type="justify-start lg:justify-end"-->
<!--                no-wrap-->
<!--              >-->
<!--                <BaseButton-->
<!--                  color="info"-->
<!--                  :icon="mdiEye"-->
<!--                />-->
<!--              </BaseButtons>-->
<!--            </td>-->
          </template>
        </paginated-table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>

</style>

