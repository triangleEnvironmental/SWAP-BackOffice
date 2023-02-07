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
import {Inertia} from "@inertiajs/inertia";
import useFilterForm from "@/Composibles/useFilterForm";
import NotificationModal from '@/Components/NotificationModal.vue'
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation('notification_presets');

const props = defineProps([
  'notification_presets',
  'institution_options'
])

const {filterForm, applyFilter} = useFilterForm({
  institution_id: null,
  keyword: null,
})

const currentNotificationPreset = ref(null)
const openNotificationModal = ref(false)

const sendNotification = (preset) => {
  currentNotificationPreset.value = preset
  openNotificationModal.value = true
}

const titleStack = ref(['Backoffice', 'Notification Presets'])

const confirmModalRef = ref()

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete notification preset',
    content: 'Are you sure to delete this notification preset?'
  })
  if (confirm) {
    Inertia.delete(
      route('notification-preset.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="Notification Presets"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <PageNotification/>

  <NotificationModal v-model="openNotificationModal"
                     v-if="currentNotificationPreset"
                     :preset="currentNotificationPreset"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-notification-preset')">
          <BaseButton label="New Notification Preset"
                      :icon="mdiPlus"
                      color="info"
                      route-name="notification-preset.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar  refreshable-data="notification_presets">
      Notification Presets
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
        <paginated-table :data="notification_presets"
                         :checkable="false"
                         :titles="['ID', 'Title', 'Description', 'Institution']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.title">
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
            <td data-label="Institution">
              <MightNA :value="row.institution">
                {{ row.institution.name_en }}
              </MightNA>
            </td>
            <td data-label="Actions">
              <BaseButtons
                type="justify-start lg:justify-end"
                no-wrap
              >
                <BaseButton
                  v-if="row.can_delete"
                  color="danger"
                  title="Delete"
                  outline
                  @click="onDelete(row.id)"
                  :icon="Icons.delete"
                />
                <BaseButton
                  color="warning"
                  v-if="row.can_notify"
                  title="Send notification"
                  :icon="Icons.notification"
                  @click="sendNotification(row)"
                />
                <BaseButton
                  v-if="row.can_update"
                  color="info"
                  :route-name="route('notification-preset.edit', {id: row.id})"
                  title="Edit"
                  :icon="Icons.edit"
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

