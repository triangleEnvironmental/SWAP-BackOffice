<script setup>
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import PaginatedTable from "@/Components/PaginatedTable.vue";
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBox from '@/Components/CardBox.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import SectionMain from '@/Components/SectionMain.vue'
import {mdiEye, mdiTextureBox, mdiPlus, mdiPencilBoxOutline} from "@mdi/js"
import {ref} from 'vue'
import PillTag from '@/Components/PillTag.vue'
import useFilterForm from "@/Composibles/useFilterForm";
import {Inertia} from "@inertiajs/inertia";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'service_providers',
])

const titleStack = ref(['Backoffice', 'Service Providers'])

const confirmModalRef = ref()

const {filterForm, applyFilter} = useFilterForm({
  keyword: null
})

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete service provider',
    content: 'Are you sure to delete this service provider?'
  })
  if (confirm) {
    Inertia.delete(
      route('service-provider.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="Service Providers"/>

  <ConfirmModal ref="confirmModalRef"/>

  <NavBarSearch v-model="filterForm.keyword" @submit="applyFilter"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-service-provider')">
          <BaseButton label="New Service Provider"
                      :icon="mdiPlus"
                      color="info"
                      route-name="service-provider.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar refreshable-data="service_providers">
      Service Providers
      <template v-slot:action>
      </template>
    </SectionHeroBar>
    <SectionMain>
      <PageNotification/>
      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="service_providers"
                         :checkable="false"
                         :titles="['ID', '', 'Name in English', 'Name in Khmer', 'Description in English', 'Description in Khmer', 'Sectors']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name_en">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td class="border-b-0 lg:w-6 before:hidden">
              <UserAvatar
                :username="row.name_en"
                :avatar="row.logo_url"
                class="w-32 h-32 mx-auto lg:w-8 lg:h-8 flex items-center"
              />
            </td>
            <td data-label="Name in English">
              {{ row.name_en }}
            </td>
            <td data-label="Name in Khmer">
              {{ row.name_km }}
            </td>
            <td data-label="Description in English">
              <MightNA :value="row.description_en"/>
            </td>
            <td data-label="Description in Khmer">
              <MightNA :value="row.description_km"/>
            </td>
            <td data-label="Sectors">
              <template v-for="sector in row.sectors">
                <PillTag small
                         class="bg-blue-600 text-white"
                         :text="sector.name_en"/>
              </template>
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
                  v-if="row.can_view_area"
                  color="info"
                  :route-name="route('service-provider-area.list', {id: row.id})"
                  title="Manage service area"
                  :icon="Icons.area"
                />
                <BaseButton
                  v-if="row.can_update"
                  color="info"
                  :route-name="route('service-provider.edit', {id: row.id})"
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

