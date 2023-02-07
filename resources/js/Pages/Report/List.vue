<script setup>
import ReportPreviewModal from "@/Components/ReportPreviewModal.vue"
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import BaseButtons from "@/Components/BaseButtons.vue";
import TableButtons from "@/Components/TableButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import FormControl from "@/Components/FormControl.vue";
import PaginatedTable from "@/Components/PaginatedTable.vue";
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBox from '@/Components/CardBox.vue'
import SectionMain from '@/Components/SectionMain.vue'
import ReportFilter from '@/Components/ReportFilter.vue'
import NavBarSearch from '@/Components/NavBarSearch.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import PillTag from '@/Components/PillTag.vue'
import {mdiEye, mdiMapSearch, mdiIncognito, mdiFilterMenuOutline} from "@mdi/js"
import useMounted from "@/Composibles/useMounted";
import {computed, ref} from 'vue'
import useFilterForm from "@/Composibles/useFilterForm";
import {Inertia} from "@inertiajs/inertia";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const {filterForm, applyFilter, applyFilterWithOption} = useFilterForm({
  sector_ids: [],
  report_type_ids: [],
  status_ids: [],
  date_range: [],
  area_ids: [],
  assigned_me: false,
  keyword: null
})

const props = defineProps([
  'reports',
  'status_options',
  'can_export_csv'
])

const previewReportModalOpen = ref(false)
const currentPreviewReport = ref(null)
const confirmModalRef = ref()

const titleStack = ref(['Backoffice', 'Reports'])

const previewReport = (report) => {
  currentPreviewReport.value = report
  previewReportModalOpen.value = true
}

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete report',
    content: 'Are you sure to delete this report?'
  })
  if (confirm) {
    Inertia.delete(
      route('report.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="Reports"/>

  <ConfirmModal ref="confirmModalRef"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <ReportPreviewModal v-if="currentPreviewReport"
                      can-view-on-map
                      :report="currentPreviewReport"
                      v-model="previewReportModalOpen">
  </ReportPreviewModal>

  <!--  <NavBarSearch placeholder="Search... (Ctrl + K)"/>-->

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack" refreshable-data="reports">
      <template v-slot:action>
        <BaseButtons>
          <BaseButton v-if="can_export_csv"
                      :href="route('report.export.csv', (() => {
                        const params = route().params
                        if (params.page !== undefined) {
                          delete params.page
                        }
                        return params
                      })())"
                      color="success"
                      :icon="Icons.export"
                      label="Export CSV"/>
          <BaseButton color="info"
                      :route-name="route('report.map', (() => {
                      const params = route().params
                      if (params.page !== undefined) {
                        delete params.page
                      }
                      return params
                    })())"
                      :icon="mdiMapSearch"
                      label="View Map"/>
        </BaseButtons>
      </template>
    </SectionTitleBar>
    <!--    <SectionHeroBar>-->
    <!--      Reports-->
    <!--      <template v-slot:action>-->
    <!--        <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"-->
    <!--                      v-if="status_options.length > 0"-->
    <!--                      v-model="filterForm.status_id"-->
    <!--                      placeholder="All statuses"-->
    <!--                      :options="status_options"-->
    <!--                      @change="applyFilter"/>-->
    <!--      </template>-->
    <!--    </SectionHeroBar>-->
    <SectionMain>

      <PageNotification/>

      <div class="grid gap-3 grid-cols-12">
        <div class="col-span-12 md:col-span-4 lg:col-span-3 order-1 md:order-2">
          <ReportFilter :form="filterForm"
                        @apply="applyFilterWithOption"/>
        </div>
        <CardBox
          class="col-span-12 md:col-span-8 lg:col-span-9 order-2 md:order-1"
          has-table
        >
          <paginated-table :data="reports"
                           :checkable="false"
                           :titles="['ID', 'Description', 'Report Type', 'Reporter', 'Province', 'Status', 'Created On']"
                           :get-id="(item) => item.id"
                           :get-label="(item) => item.name_en">
            <template v-slot:row="{row: {row, index}}">
              <td data-label="ID">
                {{ row.id }}
              </td>
              <td data-label="Description">
                <MightNA :value="row.description">
                  <p>
                    {{row.description}}
                  </p>
                </MightNA>
              </td>
              <td data-label="Report Type">
                <MightNA :value="row.report_type?.name_en"/>
              </td>
              <td data-label="Reporter">
                <template v-if="row.reporter && row.reporter.full_name">
                  {{ row.reporter.full_name }}
                </template>
                <template v-else>
                <span class="text-gray-400">
                  <BaseIcon
                    :path="mdiIncognito"
                    class="text-gray-500 dark:text-gray-400"
                  />
                </span>
                </template>
              </td>
              <td data-label="Province">
                <MightNA :value="row.province?.name_en"/>
              </td>
              <td data-label="Status">
                <MightNA :value="row.status?.name_en">
                  <PillTag v-if="row.status?.name_en"
                           :text="row.status?.name_en"
                           small
                           :color="row.status?.color"/>
                </MightNA>
              </td>
              <td data-label="Created On">
                <MightNA :value="row.created_at">
                  {{$helper.formatDate(row.created_at)}}
                </MightNA>
              </td>
              <td data-label="Actions">
                <TableButtons>
                  <BaseButton
                    v-if="row.can_delete"
                    color="danger"
                    title="Delete"
                    outline
                    @click="onDelete(row.id)"
                    :icon="Icons.delete"
                  />
                  <BaseButton
                    color="info"
                    :icon="mdiEye"
                    title="Preview"
                    outline
                    @click="previewReport(row)"
                  />
                  <BaseButton
                    color="info"
                    :icon="Icons.detail"
                    title="Detail"
                    :route-name="route('report.show', {id: row.id})"
                  />
                </TableButtons>
              </td>
            </template>
          </paginated-table>
        </CardBox>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>

</style>

