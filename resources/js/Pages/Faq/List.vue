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
import {mdiEye, mdiPencilBoxOutline, mdiPlus} from "@mdi/js"
import {ref} from 'vue'
import useHelper from "@/Composibles/useHelper";
import useFilterForm from "@/Composibles/useFilterForm";
import {Inertia} from "@inertiajs/inertia";
import PillTag from '@/Components/PillTag.vue';
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();
const {can} = useHelper();

const props = defineProps([
  'faqs',
  'sector_options',
  'faq_category_options',
])

const titleStack = ref(['Backoffice', 'FAQs'])

const {filterForm, applyFilter} = useFilterForm({
  keyword: null,
  sector_id: null,
  faq_category_id: null,
})

const confirmModalRef = ref()

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete FAQ',
    content: 'Are you sure to delete this FAQ?'
  })
  if (confirm) {
    Inertia.delete(
      route('faq.delete', {id}),
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="FAQs"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-faq')">
          <BaseButton label="New FAQ"
                      :icon="mdiPlus"
                      color="info"
                      route-name="faq.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar refreshable-data="faqs">
      FAQs
      <template v-slot:action>
        <div class="grid gap-3 grid-cols-12">
          <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"
                        v-model="filterForm.sector_id"
                        placeholder="Filter by a sector"
                        :options="sector_options"
                        @change="applyFilter"/>

          <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"
                        v-model="filterForm.faq_category_id"
                        placeholder="Filter by categories"
                        :options="faq_category_options"
                        @change="applyFilter"/>
        </div>
      </template>
    </SectionHeroBar>
    <SectionMain>
      <CardBox
        class="mb-6"
        has-table
      >
        <paginated-table :data="faqs"
                         :checkable="false"
                         :titles="['ID', 'Question in English', 'Question in Khmer', 'Sector', 'Categories']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td data-label="Question in English">
              <MightNA :value="row.question_en"/>
            </td>
            <td data-label="Question in Khmer">
              <MightNA :value="row.question_km"/>
            </td>
            <td data-label="Sector">
              <MightNA :value="row.sector?.name_en">
                <div class="flex items-center">
                  <img :src="row.sector.icon_url" width="30" alt="Sector image">
                  <span class="ml-2">{{ row.sector.name_en }}</span>
                </div>
              </MightNA>
            </td>
            <td data-label="Categories">
              <MightNA :value="row.categories.length > 0">
                <template v-for="category in row.categories">
                  <PillTag :text="category.name_en" small class="bg-blue-500 text-white"/>
                </template>
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
                  :route-name="route('faq.edit', {id: row.id})"
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

