<template>
  <ResponsiveTable :items="items" :titles="titles" :checkable="checkable" :get-label="getLabel" :get-id="getId">
    <template v-slot:row="{row}">
      <slot name="row" v-bind:row="row"></slot>
    </template>
  </ResponsiveTable>

  <div
    class="p-3 lg:px-6 border-t border-gray-100 dark:border-gray-800"
  >
    <div class="justify-between items-center block md:flex my-6">
      <div class="flex grow items-center justify-center">
        <BaseButtons>
          <BaseButton
            v-for="page in pagesList"
            :key="page"
            :active="page.active"
            :label="page.label"
            :small="true"
            :route-name="page.url"
          />
        </BaseButtons>
      </div>
      <div class="flex shrink-0 grow-0 items-center justify-center mt-4 md:mt-0">
        <small>Page {{ currentPageHuman }} of {{ numPages }} (total {{totalRecords}})</small>
      </div>
    </div>
  </div>

</template>

<script setup>
import ResponsiveTable from '@/Components/ResponsiveTable.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import {computed, ref} from "vue";

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },

  titles: {
    type: Array,
    required: true,
  },

  getLabel: {
    type: Function,
    default: (item) => item.name ?? item,
  },

  getId: {
    type: Function,
    default: (item) => item.id ?? item,
  },

  checkable: {
    type: Boolean,
    default: true,
  }
})

const items = computed(() => props.data.data)

const perPage = ref(props.data.per_page)

const currentPage = ref(props.data.current_page)

const numPages = computed(() => props.data.last_page)

const totalRecords = computed(() => props.data.total)

const currentPageHuman = computed(() => props.data.current_page)

const pagesList = computed(() => props.data.links)
</script>

<style scoped>

</style>
