<template>
  <Head title="Service Area"/>

  <NavBarSearch v-model="filterForm.keyword" @submit="applyFilter"/>

  <ConfirmModal ref="confirmModalRef"/>

  <ServiceAreaModal v-model="openServiceAreaModal"
                    :data="currentPreviewArea"
                    institution-type="service-provider">
  </ServiceAreaModal>

  <NotificationAreaModal v-model="openNotificationModal"
                         v-if="currentNotificationArea"
                         :data="currentNotificationArea"/>

  <CardBoxModal
    v-if="currentPreviewArea"
    v-model="previewAreaModalOpen"
    :title="'Service Area on Map'"
    :small="false"
    :show-setting-button="false"
    :has-button="false"
    :has-cancel="true"
  >
    <template v-slot:default>
      <div style="height: 60vh;">
        <AreaMap ref="areaMapRef"
                 :center="currentPreviewArea.centroid ? $helper.getPoint(currentPreviewArea.centroid) : null"
                 :geo-json="currentPreviewArea.area"/>
      </div>
    </template>

    <template v-slot:buttons>
      <BaseButton @click="areaMapRef.fitBound()" label="Zoom to fit" color="info"/>
    </template>
  </CardBoxModal>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('update-service-provider-area')">
          <BaseButton label="New Service Area"
                      :icon="mdiPlus"
                      @click="createArea"
                      color="info"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar>
      Service Area
      <template v-slot:action>
      </template>
    </SectionHeroBar>
    <SectionMain>

      <CardBox
        class="mb-6"
        has-table
      >
        <PageNotification/>

        <paginated-table :data="data"
                         :checkable="false"
                         :titles="['ID', 'Name in English', 'Name in Khmer']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name_en">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td data-label="Name in English">
              {{ row.name_en }}
            </td>
            <td data-label="Name in Khmer">
              {{ row.name_km }}
            </td>
            <td data-label="Actions">
              <BaseButtons
                type="justify-start lg:justify-end"
                no-wrap
              >
                <BaseButton
                  v-if="row.can_delete"
                  color="danger"
                  @click="onDelete(row.id)"
                  title="Delete"
                  outline
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
                  color="info"
                  title="View on map"
                  :icon="mdiMapSearch"
                  @click="showOnMap(row)"
                />

                <BaseButton
                  v-if="row.can_update"
                  color="info"
                  title="Edit"
                  @click="editArea(row)"
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

<script setup>
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import PaginatedTable from "@/Components/PaginatedTable.vue";
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBox from '@/Components/CardBox.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import AreaMap from '@/Components/AreaMap.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import SectionMain from '@/Components/SectionMain.vue'
import {mdiEye, mdiMapSearch, mdiPlus, mdiPencilBoxOutline} from "@mdi/js"
import {ref} from 'vue'
import PillTag from '@/Components/PillTag.vue'
import ServiceAreaModal from '@/Components/ServiceAreaModal.vue'
import NotificationAreaModal from '@/Components/NotificationAreaModal.vue'
import useFilterForm from "@/Composibles/useFilterForm";
import {Inertia} from "@inertiajs/inertia";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'data',
  'institution',
])

const titleStack = ref([props.institution.name_en, 'Service Area'])
const confirmModalRef = ref()

const {filterForm, applyFilter} = useFilterForm({
  keyword: null
})

const areaMapRef = ref(null)
const currentPreviewArea = ref(null)
const currentNotificationArea = ref(null)
const previewAreaModalOpen = ref(false)

const openServiceAreaModal = ref(false)
const openNotificationModal = ref(false)

const sendNotification = (area) => {
  currentNotificationArea.value = area
  openNotificationModal.value = true
}

const showOnMap = (area) => {
  currentPreviewArea.value = area
  previewAreaModalOpen.value = true
}

const createArea = () => {
  currentPreviewArea.value = null
  openServiceAreaModal.value = true
}

const editArea = (area) => {
  currentPreviewArea.value = area
  openServiceAreaModal.value = true
}

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete service area',
    content: 'Are you sure to delete this service area?'
  })
  if (confirm) {
    Inertia.delete(
      route(
        'service-provider-area.delete',
        {
          id: route().params.id,
          area_id: id
        }
      ),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<style scoped>

</style>
