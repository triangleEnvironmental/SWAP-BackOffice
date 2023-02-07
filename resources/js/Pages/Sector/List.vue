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
import UserAvatar from '@/Components/UserAvatar.vue'
import {Inertia} from "@inertiajs/inertia";
import useFilterForm from "@/Composibles/useFilterForm";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'sectors',
])

const titleStack = ref(['Backoffice', 'Sectors'])
const confirmModalRef = ref()

const {filterForm, applyFilter} = useFilterForm({
  keyword: null
})

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete sector',
    content: 'Are you sure to delete this sector?'
  })
  if (confirm) {
    Inertia.delete(
      route('sector.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}
</script>

<template>
  <Head title="Sectors"/>

  <NavBarSearch v-model="filterForm.keyword" @submit="applyFilter"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-sector')">
          <BaseButton label="New Sector"
                      :icon="mdiPlus"
                      color="info"
                      route-name="sector.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar refreshable-data="sectors">
      Sectors
      <template v-slot:action>
        <!--        Filter here-->
      </template>
    </SectionHeroBar>
    <SectionMain>

      <PageNotification/>

      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="sectors"
                         :checkable="false"
                         :titles="['ID', '', 'Name in English', 'Name in Khmer', '']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name_en">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td class="border-b-0 lg:w-6 before:hidden">
              <UserAvatar
                v-if="row.icon_url"
                :username="row.name_en"
                :avatar="row.icon_url"
                class="w-24 h-24 mx-auto lg:w-6 lg:h-6"
              />
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
                  title="Delete"
                  outline
                  @click="onDelete(row.id)"
                  :icon="Icons.delete"
                />
                <BaseButton
                  v-if="row.can_update"
                  color="info"
                  :route-name="route('sector.edit', {id: row.id})"
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

