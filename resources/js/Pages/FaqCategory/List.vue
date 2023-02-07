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
import {Inertia} from "@inertiajs/inertia";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'faq_categories',
])

const titleStack = ref(['Backoffice', 'FAQ Categories'])

const {filterForm, applyFilter} = useFilterForm({
  keyword: null,
})

const confirmModalRef = ref()

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete FAQ category',
    content: 'Are you sure to delete this FAQ category?'
  })
  if (confirm) {
    Inertia.delete(
      route('faq-category.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="FAQ Categories"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-faq-category')">
          <BaseButton label="New FAQ Category"
                      :icon="mdiPlus"
                      color="info"
                      route-name="faq-category.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar refreshable-data="faq_categories">
      FAQ Categories
      <template v-slot:action>
      </template>
    </SectionHeroBar>
    <SectionMain>
      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="faq_categories"
                         :checkable="false"
                         :titles="['ID', 'Name in English', 'Name in Khmer', '']"
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
                  title="Delete"
                  outline
                  @click="onDelete(row.id)"
                  :icon="Icons.delete"
                />
                <BaseButton
                  v-if="row.can_update"
                  color="info"
                  :route-name="route('faq-category.edit', {id: row.id})"
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

