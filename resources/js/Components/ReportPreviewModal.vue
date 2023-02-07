<template>
  <CardBoxModal
    v-if="report"
    v-model="computedValue"
    :title="'Preview Report'"
    :small="false"
    :has-button="false"
    :has-cancel="true"
    :show-setting-button="false"
  >
    <div class="grid grid-cols-12 gap-5">
      <div class="col-span-12 md:col-span-7">
        <template v-if="report.medias && report.medias.length > 0">
          <ImageSlider :image-urls="report.medias.map(e => e.media_url)"
                       image-style="max-height: 60vh;">
          </ImageSlider>
        </template>
        <blockquote class="bg-gray-50 py-3 px-2 border-l-4">
          <p>
            {{ report.description }}
          </p>
        </blockquote>
      </div>
      <div class="col-span-12 md:col-span-5">
        <CardBox :icon="Icons.information"
                 title="Summary"
                 has-table>
          <table>
            <tr>
              <th class="hidden lg:table-cell">Created on</th>
              <td data-label="Created on">
                <MightNA :value="report.created_at">
                  {{ $helper.formatDate(report.created_at) }}
                </MightNA>
              </td>
            </tr>
            <tr>
              <th class="hidden lg:table-cell">Report type</th>
              <td data-label="Report type">
                <MightNA :value="report.report_type?.name_en"/>
              </td>
            </tr>
            <tr>
              <th class="hidden lg:table-cell">Status</th>
              <td data-label="Status">
                <MightNA :value="report.status?.name_en">
                  <strong :style="{color: report.status?.color}">{{
                      report.status?.name_en
                    }}</strong>
                </MightNA>
              </td>
            </tr>
            <tr v-if="report.province">
              <th class="hidden lg:table-cell">{{ report.province.label }}</th>
              <td data-label="Province/City">
                <MightNA :value="report.province.name_en"/>
              </td>
            </tr>
            <tr>
              <th class="hidden lg:table-cell">Reported by</th>
              <td data-label="Reported by">
                <strong class="flex items-center" v-if="report.reporter">
                  <UserAvatar class="w-8 h-8 mr-2" :username="report.reporter.full_name"
                              :avatar="report.reporter.profile_photo_url"/>
                  <span v-if="report.reporter.full_name">
                  {{ report.reporter.full_name }}
                </span>
                  <span v-else class="text-gray-500">
                  (No name)
                </span>
                </strong>
                <span v-else>
            <i class="text-gray-500 dark:text-gray-400">(Anonymous)</i>
          </span>
              </td>
            </tr>
            <tr>
              <th class="hidden lg:table-cell">Assigned to</th>
              <td data-label="Assigned to">
                <MightNA :value="report.assignee">
                  <strong class="flex items-center">
                    <UserAvatar class="w-8 h-8 mr-2" :username="report.assignee.full_name"
                                :avatar="report.assignee.profile_photo_url"/>
                    <span>{{ report.assignee.full_name }}</span>
                  </strong>
                </MightNA>
              </td>
            </tr>
          </table>
        </CardBox>

        <CardBox
          :key="report.id"
          :icon="Icons.comment"
          title="Comments"
          class="mt-4"
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
                       :initial-url="route('comment.list', {report_id: report.id})"/>

        </CardBox>
      </div>
    </div>

    <template v-slot:buttons>
      <BaseButton
        :icon="mdiFileFind"
        label="View details"
        :route-name="route('report.show', {id: report.id})"
        color="info"
      />
      <BaseButton
        v-if="canViewOnMap"
        :icon="mdiMapSearch"
        label="View on map"
        :route-name="route('report.map', {id: report.id})"
        color="info"
      />
      <slot name="buttons"></slot>
    </template>
  </CardBoxModal>

  <CommentFormModal v-model="commentModalOpen"
                    @submit="onSubmitComment"/>
</template>

<script setup>
import CardBoxModal from "@/Components/CardBoxModal.vue"
import CommentFormModal from "@/Components/CommentFormModal.vue"
import BaseButton from "@/Components/BaseButton.vue"
import ImageSlider from '@/Components/ImageSlider.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import CommentList from '@/Components/CommentList.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import { mdiMapSearch, mdiFileFind } from "@mdi/js"
import {computed, ref} from "vue";
import {useForm} from "@inertiajs/inertia-vue3";

const emit = defineEmits(['update:modelValue'])

const commentModalOpen = ref()
const commentListRef = ref()

const props = defineProps({
  modelValue: [Boolean, String, null, Object],
  report: {
    type: Object,
    required: true,
  },
  canViewOnMap: {
    type: Boolean,
    default: false
  }
})

const computedValue = computed({
  get: () => props.modelValue,
  set: value => {
    emit('update:modelValue', value)
  }
})

const onOpenCommentForm = () => {
  commentModalOpen.value = true
}

const onSubmitComment = async (comment) => {
  commentModalOpen.value = false
  useForm({
    text: comment.text,
    images: comment.images,
  }).post(
    route('comment.post', {report_id: props.report.id}),
    {
      preserveScroll: true,
      onSuccess: () => {
        commentListRef.value?.updateNewComments()
      }
    }
  )
}
</script>

<style scoped>

</style>
