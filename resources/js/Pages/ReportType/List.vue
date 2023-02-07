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
import SelectForm from '@/Components/SelectForm.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import PillTag from '@/Components/PillTag.vue'
import {mdiEye, mdiPlus} from "@mdi/js"
import {ref} from 'vue'
import useFilterForm from "@/Composibles/useFilterForm";
import {Inertia} from "@inertiajs/inertia";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'report_types',
  'sector_options',
])

const {filterForm, applyFilter} = useFilterForm({
  sector_id: null,
  keyword: null,
})

const titleStack = ref(['Backoffice', 'Report Types'])

const confirmModalRef = ref()

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete report type',
    content: 'Are you sure to delete this report type?'
  })
  if (confirm) {
    Inertia.delete(
      route('report-type.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="Report Types"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-report-type')">
          <BaseButton label="New Report Type"
                      :icon="mdiPlus"
                      color="info"
                      route-name="report-type.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar refreshable-data="report_types">
      Report Types
      <template v-slot:action>
        <FilterOption v-model="filterForm.sector_id"
                      placeholder="Filter by a sector"
                      :options="sector_options"
                      @change="applyFilter"/>
      </template>
    </SectionHeroBar>
    <SectionMain>
      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="report_types"
                         :checkable="false"
                         :titles="['ID', 'Name in English', 'Name in Khmer', 'Is Private', 'Sector']"
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
            <td data-label="Is Private">
              <template v-if="row.is_private">
                <span class="flex items-center">
                  <BaseIcon :path="Icons.private" class="text-red-600"/>
                  <span>private</span>
                </span>
              </template>
              <template v-else>
                <span class="flex items-center">
                  <BaseIcon :path="Icons.public" class="text-green-600"/>
                  <span>public</span>
                </span>
              </template>
            </td>
            <td data-label="Sector">
              <MightNA :value="row.sector?.name_en">
                <div class="flex items-center">
                  <img :src="row.sector.icon_url" width="30" alt="Sector image">
                  <span class="ml-2">{{ row.sector.name_en }}</span>
                </div>
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
                  v-if="row.can_update"
                  color="info"
                  :route-name="route('report-type.edit', {id: row.id})"
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

