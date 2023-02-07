<template>
  <Head title="Report Details"/>

  <ConfirmModal ref="confirmModalRef"/>

  <CommentFormModal v-model="commentModalOpen"
                    @submit="onSubmitComment"/>

  <ChangeReportStatusModal v-if="report"
                           v-model="reportStatusModalOpen"
                           :report="report"
                           @submit="onSubmitStatus"
                           :options="status_options"/>

  <AssignUserModal v-model="assigneeModalOpen"
                   v-if="report && assignableUsersTable"
                   :user-table="assignableUsersTable"
                   @changed="assignNewUser"
                   :active-id="report.assignee_id"/>

  <LayoutAuthenticated>
    <!--    In case unauthorized or 404 -->
    <template v-if="error_message">
      <div class="h-full flex items-center justify-center">
        <div class="ml-4 text-lg text-gray-500 uppercase tracking-wider">
          <div>
            {{ error_message }}
          </div>
          <div class="text-center mt-4">
            <BaseButton @click="window.history.back()"
                        :icon="Icons.back"
                        color="info"
                        label="Back"/>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <SectionTitleBar :title-stack="titleStack">
        <template v-slot:action>
          <BaseButtons>
            <BaseButton v-if="report.can_moderate" @click="onChangeStatus" :icon="mdiListStatus" label="Change Status"
                        color="warning"/>
            <BaseButton v-if="report.can_delete" @click="onDelete" :icon="Icons.delete" title="Delete this report"
                        color="danger" outline/>
          </BaseButtons>
        </template>
      </SectionTitleBar>
      <!--    <SectionHeroBar>-->
      <!--      Reports-->
      <!--      <template v-slot:action>-->
      <!--      </template>-->
      <!--    </SectionHeroBar>-->
      <SectionMain>

        <PageNotification/>

        <div class="grid gap-3 grid-cols-12">
          <div class="col-span-12 md:col-span-8 lg:col-span-9 order-1 md:order-1">
            <CardBox>
              <BaseLevel type="justify-between items-start">
                <BaseLevel type="justify-start">
                  <UserAvatar class="w-12 h-12 md:mr-6"
                              :avatar="report.reporter?.profile_photo_url"
                              :username="report.reporter?.full_name ?? 'Anonymous'"/>
                  <div class="text-center md:text-left overflow-hidden">
                    <template v-if="report.reporter">
                      <h4 class="text-xl text-ellipsis">
                    <span v-if="report.reporter.full_name">
                      {{ report.reporter.full_name }}
                    </span>
                        <span v-else class="text-xl text-ellipsis text-gray-500">
                      (No name)
                    </span>
                      </h4>
                    </template>
                    <template v-else>
                      <h4 class="text-xl text-ellipsis text-gray-500">
                        (Anonymous)
                      </h4>
                    </template>
                    <p class="text-gray-500 dark:text-slate-400">
                      <template v-if="report.created_at">
                        {{ $helper.formatDate(report.created_at) }}
                      </template>
                    </p>
                    <PillTag v-if="report.status"
                             :text="report.status.name_en"
                             :color="report.status.color"
                             small/>
                  </div>
                </BaseLevel>

                <BaseButtons>
                  <div class="mr-3">
                    <template v-if="report.assignee">
                      <BaseButton :icon="Icons.account"
                                  @click="onAssign"
                                  color="info"
                                  outline
                                  :label="report.assignee.name"/>
                    </template>
                    <template v-else>
                      <BaseButton :icon="Icons.assign"
                                  @click="onAssign"
                                  color="warning"
                                  outline label="Unassigned"/>
                    </template>
                  </div>
                  <BaseButton
                    v-if="report.location"
                    :icon="Icons.findLocation"
                    title="View on map"
                    :route-name="route('report.map', {id: report.id})"
                    color="info"
                  />
<!--                  <BaseButton-->
<!--                    v-if="report.location"-->
<!--                    :icon="Icons.googleMap"-->
<!--                    title="Google Map"-->
<!--                    color="info"-->
<!--                    target="_blank"-->
<!--                    :href="`https://www.google.com/maps/search/?api=1&query=${report.location.coordinates[1]},${report.location.coordinates[0]}`"-->
<!--                    outline-->
<!--                  />-->
                </BaseButtons>
              </BaseLevel>

              <div v-if="report.province" class="my-4 text-center md:text-left">
                <div class="inline-block">
                <span class="text-gray-500 dark:text-gray-400">
                  {{ report.province.label }} :
                </span>
                  <strong class="ml-2 py-1 px-4 rounded-full bg-gray-200 dark:bg-gray-800 font-bold">
                    {{ report.province.name_en }}
                  </strong>
                </div>
              </div>

              <ul class="mt-4 flex items-center justify-center md:justify-start">
                <li v-if="report.sector"
                    class="flex section-title-bar-stack-item inline-block pr-3 text-gray-500 dark:text-gray-400 last:pr-0 last:font-black last:text-black"
                >
              <span class="inline-block">
                <span class="flex items-center">
                <img v-if="report.sector.icon_url"
                     class="inline-block"
                     :src="report.sector.icon_url"
                     width="26"
                     alt="Sector logo">
                <span class="ml-2">
                  {{ report.sector.name_en }}
                </span>
              </span>
              </span>
                </li>
                <li v-if="report.report_type"
                    class="flex section-title-bar-stack-item inline-block pr-3 text-gray-500 dark:text-gray-400 last:pr-0 last:font-black last:text-black"
                >
                  {{ report.report_type.name_en }}
                </li>
              </ul>

              <blockquote class="bg-gray-50 dark:bg-gray-800 py-3 px-2 border-l-4 mt-6 dark:border-gray-500">
                <p>
                  {{ report.description }}
                </p>
              </blockquote>

              <photo-provider>
                <div class="grid grid-cols-12 mt-6 gap-3">
                  <div v-for="media in report.medias"
                       class="col-span-12 sm:col-span-6 md:col-span-4">
                    <photo-consumer :src="media.media_url">
                      <img class="object-cover rounded-2xl"
                           :src="media.media_url"
                           alt="Report photo">
                    </photo-consumer>
                  </div>
                </div>
              </photo-provider>


              <h4 class="font-bold flex items-center text-lg mt-8 mb-4">
                <BaseIcon size="26" :path="Icons.history"/>
                <span class="ml-3">Moderation History</span>
              </h4>

              <Timeline theme="#2563eb">
                <TimelineItem v-for="history in moderation_histories" bg-color="transparent" font-color="black">
                  <template #others>
                    <template v-if="history.moderator">
                      <UserAvatar :username="history.moderator.full_name ?? ''"
                                  :avatar="history.moderator.profile_photo_url ?? ''"/>
                    </template>
                    <template v-else>
                      <div class="rounded-full w-10 h-10 bg-gray-200 flex items-center justify-center">
                        <BaseIcon :path="Icons.account"/>
                      </div>
                    </template>
                  </template>
                  <div class="flex items-start">
                    <div class="grow">
                      <template v-if="history.moderator">
                        <h5>
                          <span class="font-bold">{{ history.moderator.full_name }}</span>
                          <span v-if="history.moderator.institution"
                                class="ml-3">({{ history.moderator.institution.name_en }})</span>
                        </h5>
                      </template>
                      <template v-else>
                        <h5 class="font-bold text-gray-500">(Deleted user)</h5>
                      </template>
                      <p class="text-gray-400">
                        {{ $helper.fromNow(history.created_at) }}
                      </p>
                      <p class="text-black dark:text-white">
                        <span>Changed report status from</span>
                        <PillTag class="ml-3 font-bold"
                                 small
                                 :color="history.from_status.color"
                                 :text="history.from_status.name_en"></PillTag>
                        <span>to</span>
                        <PillTag class="ml-3 font-bold"
                                 small
                                 :color="history.to_status.color"
                                 :text="history.to_status.name_en"></PillTag>
                      </p>
                    </div>
                    <div v-if="history.master_notification">
                      <VDropdown>
                        <BaseButton small outline :icon="Icons.notificationMessage" color="info"/>
                        <template #popper>
                          <div class="p-4">
                            <h5 class="font-bold">
                              {{ history.master_notification.title }}
                            </h5>
                            <div>
                              {{ history.master_notification.description }}
                            </div>
                          </div>
                        </template>
                      </VDropdown>
                    </div>
                  </div>
                </TimelineItem>

                <TimelineItem bg-color="transparent" font-color="black">
                  <template #others>
                    <template v-if="report.reporter">
                      <UserAvatar :username="report.reporter.full_name" :avatar="report.reporter.profile_photo_url"/>
                    </template>
                    <template v-else>
                      <div class="rounded-full w-10 h-10 bg-gray-200 flex items-center justify-center">
                        <BaseIcon :path="Icons.anonymous"/>
                      </div>
                    </template>
                  </template>
                  <div>
                    <h5 class="font-bold">
                      <template v-if="report.reporter">
                        <span v-if="report.reporter.full_name">
                          {{ report.reporter.full_name }}
                        </span>
                        <span v-else class="text-gray-500">
                          (No name)
                        </span>
                      </template>
                      <span v-else class="text-gray-500">
                          (Anonymous)
                        </span>
                    </h5>
                    <p class="text-gray-400">
                      {{ $helper.fromNow(report.created_at) }}
                    </p>
                    <p class="text-black dark:text-white">Created the report</p>
                  </div>
                </TimelineItem>
              </Timeline>

            </CardBox>
          </div>
          <div class="col-span-12 md:col-span-4 lg:col-span-3 order-2 md:order-1">
            <CardBox
              :icon="Icons.comment"
              title="Comments"
              has-table
            >

              <div v-if="report.can_comment" class="p-6 md:p-3">
                <div
                  class="rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 hover:dark:bg-gray-700 cursor-pointer px-4 py-3 text-gray flex"
                  @click="onOpenCommentForm">
                  <span class="grow truncate">
                    Write a comment ...
                  </span>
                  <BaseIcon :path="Icons.camera"/>
                </div>
              </div>

              <CommentList ref="commentListRef"
                           :initial-url="route('comment.list', {report_id: route().params.id})"/>

            </CardBox>
          </div>
        </div>
      </SectionMain>
    </template>
  </LayoutAuthenticated>
</template>

<script setup>
import ReportPreviewModal from "@/Components/ReportPreviewModal.vue"
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import CommentFormModal from "@/Components/CommentFormModal.vue";
import FormControl from "@/Components/FormControl.vue";
import AssignUserModal from "@/Components/AssignUserModal.vue";
import CommentList from "@/Components/CommentList.vue";
import ChangeReportStatusModal from "@/Components/ChangeReportStatusModal.vue";
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBox from '@/Components/CardBox.vue'
import SectionMain from '@/Components/SectionMain.vue'
import NavBarSearch from '@/Components/NavBarSearch.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import PillTag from '@/Components/PillTag.vue'
import {mdiEye, mdiMapSearch, mdiIncognito, mdiFilterMenuOutline, mdiAccountPlusOutline, mdiListStatus} from "@mdi/js"
import useMounted from "@/Composibles/useMounted";
import {computed, onMounted, ref} from 'vue'
import useFilterForm from "@/Composibles/useFilterForm";
import {Inertia} from "@inertiajs/inertia";
import {useToast} from "vue-toastification";
import {useForm} from "@inertiajs/inertia-vue3";
import {Timeline, TimelineTitle, TimelineItem} from 'vue3-timeline'


const props = defineProps([
  'error_message',
  'report',
  'status_options',
  'moderation_histories',
])

const toast = useToast()
const titleStack = ref(['Reports', 'Report Details'])
const assignableUsersTable = ref(null)
const assigneeModalOpen = ref(null)
const confirmModalRef = ref()
const commentModalOpen = ref()
const reportStatusModalOpen = ref()
const commentListRef = ref()

const onChangeStatus = async () => {
  reportStatusModalOpen.value = true
}

const onDelete = async () => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete report',
    content: 'Are you sure to delete this report?'
  })
  if (confirm) {
    Inertia.delete(
      route('report.delete', {id: route().params.id}),
      {
        preserveScroll: true,
        // onSuccess: () => {
        //   window.history.back();
        // }
      }
    )
  }
}

let requestingAssignmentList = false;

const fetchAssignableUsers = async () => {
  try {
    requestingAssignmentList = true;
    const response = await axios.get(route('assignment-user.list', {report_id: route().params.id}))
    assignableUsersTable.value = response.data.data
    requestingAssignmentList = false;
  } catch (e) {
    toast.error(e.toString());
  }
}

const onOpenCommentForm = () => {
  commentModalOpen.value = true
}

const onAssign = async () => {
  if (props.report.can_assign) {
    if (assignableUsersTable.value) {
      assigneeModalOpen.value = true
    } else {
      if (requestingAssignmentList) {
        toast.warning('Data is not ready, please try again.')
      } else {
        toast.error('Failed to fetch the list of available assignees')
      }
    }
  } else {
    toast.warning('You are not allowed to assign a user')
  }
}

const assignNewUser = async (userId) => {
  assigneeModalOpen.value = false
  Inertia.post(
    route('assign.post', {report_id: route().params.id}),
    {
      user_id: userId,
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['report', 'jetstream'],
      onSuccess: () => {
        assigneeModalOpen.value = false
      }
    },
  )
}

const onSubmitStatus = async (statusForm) => {
  statusForm.post(
    route('moderation.post', {report_id: route().params.id}),
    {
      preserveScroll: true,
      onSuccess: () => {
        reportStatusModalOpen.value = false
      }
    }
  )
}

const onSubmitComment = async (comment) => {
  commentModalOpen.value = false
  useForm({
    text: comment.text,
    images: comment.images,
  }).post(
    route('comment.post', {report_id: route().params.id}),
    {
      preserveScroll: true,
      onSuccess: () => {
        commentListRef.value?.updateNewComments()
      }
    }
  )
}

onMounted(() => {
  if (props.report?.can_assign) {
    fetchAssignableUsers()
  }
})

</script>

<style scoped>

</style>
