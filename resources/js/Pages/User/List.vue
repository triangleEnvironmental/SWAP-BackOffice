<script setup>
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import PaginatedTable from "@/Components/PaginatedTable.vue";
import PillTag from "@/Components/PillTag.vue";
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBox from '@/Components/CardBox.vue'
import SectionMain from '@/Components/SectionMain.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import {mdiEye, mdiPencilBoxOutline, mdiPlus, mdiFilterMenuOutline} from "@mdi/js"
import {Inertia} from '@inertiajs/inertia'
import {ref, computed} from 'vue'
import useHelper from "@/Composibles/useHelper";
import useFilterForm from "@/Composibles/useFilterForm";
import FormControl from '@/Components/FormControl.vue'
import {useForm} from "@inertiajs/inertia-vue3";
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const {filterForm, applyFilter} = useFilterForm({
  role_id: null,
  institution_id: null,
  keyword: null,
});

const {can} = useHelper();

const props = defineProps([
  'users',
  'role_options',
  'institution_options',
])

const createUser = () => {
  Inertia.visit(route('user.create'))
}

const titleStack = ref(['Backoffice', 'Users'])

const confirmModalRef = ref()

const setActiveUser = async (id, isActive) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: isActive ? 'Activate user' : 'Deactivate user',
    content: isActive ? 'Are you sure to activate this user?' : 'Are you sure to deactivate this user?'
  })
  if (confirm) {
    Inertia.post(
      isActive ? route('user.enable', {id}) : route('user.disable', {id}),
      {},
      {
        preserveScroll: true,
      }
    )
  }
}

</script>

<template>
  <Head title="Users"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <ConfirmModal ref="confirmModalRef"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack">
      <template v-slot:action>
        <template v-if="$helper.can('create-user')">
          <BaseButton label="New User"
                      :icon="mdiPlus"
                      color="info"
                      route-name="user.create"></BaseButton>
        </template>
      </template>
    </SectionTitleBar>
    <SectionHeroBar refreshable-data="users">
      Users
      <template v-slot:action>
        <div class="grid gap-3 grid-cols-12">
          <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"
                        v-if="institution_options.length > 0"
                        v-model="filterForm.institution_id"
                        placeholder="All institutions"
                        :options="institution_options"
                        @change="applyFilter"/>

          <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"
                        :class="{
                          'sm:col-start-7': institution_options.length === 0,
                        }"
                        v-if="role_options.length > 0"
                        v-model="filterForm.role_id"
                        placeholder="Filter by a role"
                        :options="role_options"
                        @change="applyFilter"/>

        </div>
      </template>
    </SectionHeroBar>

    <SectionMain>
      <PageNotification/>

      <CardBox
        class="mb-6"
        has-table
      >

        <paginated-table :data="users"
                         :checkable="false"
                         :titles="['ID', '', 'Name', 'E-mail', 'Institution', 'Role']"
                         :get-id="(item) => item.id"
                         :get-label="(item) => item.name">
          <template v-slot:row="{row: {row, index}}">
            <td data-label="ID">
              {{ row.id }}
            </td>
            <td class="border-b-0 lg:w-6 before:hidden">
              <UserAvatar
                :username="row.full_name"
                :avatar="row.profile_photo_url"
                class="w-24 h-24 mx-auto lg:w-6 lg:h-6"
              />
            </td>
            <td data-label="Name">
              {{ row.name }}
            </td>
            <td data-label="Name">
              <MightNA :value="row.email"/>
            </td>
            <td data-label="Institution">
              <MightNA :value="row.institution?.name_en">
                <PillTag :icon="row.institution.is_municipality ? Icons.municipality : Icons.serviceProvider"
                         :text="row.institution.name_en"/>
              </MightNA>
            </td>
            <td data-label="Role">
              <MightNA :value="row.role?.name_en">
                <PillTag small
                         class="bg-blue-600 text-white"
                         :text="row.role.name_en"/>
              </MightNA>
            </td>
            <td data-label="Actions">
              <BaseButtons
                type="justify-start lg:justify-end"
                no-wrap
              >
                <template v-if="row.can_enable">
                  <template v-if="row.is_active">
                    <BaseButton
                      color="success"
                      outline
                      @click="setActiveUser(row.id, false)"
                      title="Click to disable"
                      :icon="Icons.disable"
                    />
                  </template>
                  <template v-else>
                    <BaseButton
                      color="danger"
                      outline
                      @click="setActiveUser(row.id, true)"
                      title="Click to enable"
                      :icon="Icons.enable"
                    />
                  </template>
                </template>
                <BaseButton
                  v-if="row.can_update"
                  color="info"
                  :route-name="route('user.edit', {id: row.id})"
                  title="Edit"
                  :icon="mdiPencilBoxOutline"
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

