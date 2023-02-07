<template>
  <CardBox :icon="Icons.filter"
           title="Filters">

    <template v-if="filter_report_id">
      <PillTag class="mb-4 bg-blue-600 text-white"
               :icon="Icons.report"
               :text="filter_report_id"
               @close="closeFilterOne"
               can-close/>
    </template>

    <div class="font-bold flex items-center h-8">
      <span class="grow flex items-center">
        <BaseIcon :path="Icons.sector"/>
        <span class="ml-2">Sectors</span>
      </span>

      <BaseButton v-if="form.sector_ids?.length > 0"
                  @click="form.sector_ids = [];"
                  :icon="Icons.clear"
                  color="warning"
                  outline
                  small/>
    </div>

    <FormCheckRadioPicker class="mt-3"
                          name="Sectors"
                          v-model="form.sector_ids"
                          type="checkbox"
                          :options="$helper.arrayToObject(sector_options, 'id', 'name_en')"/>

    <template v-if="form.sector_ids.length === 1 && report_type_options.length > 0">
      <div class="font-bold flex items-center mt-6 h-8">
        <span class="grow flex items-center">
          <BaseIcon :path="Icons.type"/>
          <span class="ml-2">Report types</span>
        </span>

        <BaseButton v-if="form.report_type_ids?.length > 0"
                    @click="form.report_type_ids = []"
                    :icon="Icons.clear"
                    color="warning"
                    outline small/>
      </div>

      <FilterOption v-model="form.report_type_ids"
                    class="mt-3"
                    multiple
                    :options="report_type_options"/>
    </template>

    <div class="font-bold flex items-center mt-6 h-8">
      <span class="grow flex items-center">
        <BaseIcon :path="Icons.status"/>
        <span class="ml-2">Report statuses</span>
      </span>

      <BaseButton v-if="form.status_ids?.length > 0"
                  @click="form.status_ids = []"
                  :icon="Icons.clear"
                  color="warning"
                  outline small/>
    </div>

    <FormCheckRadioPicker class="mt-3"
                          name="Statuses"
                          v-model="form.status_ids"
                          type="checkbox"
                          :options="$helper.arrayToObject(status_options, 'id', 'name_en')"/>

    <template v-if="province_options && province_options.length > 0">
      <div class="font-bold flex items-center mt-6 mb-3 h-8">
        <span class="grow flex items-center">
          <BaseIcon :path="Icons.municipality"/>
          <span class="ml-2">Provinces</span>
        </span>
        <BaseButton v-if="form.area_ids?.length > 0"
                    @click="form.area_ids = []"
                    :icon="Icons.clear"
                    color="warning"
                    outline small/>
      </div>

      <FilterOption class="col-span-12 sm:col-span-6 w-full sm:w-auto"
                    v-if="province_options.length > 0"
                    v-model="form.area_ids"
                    placeholder="Select provinces"
                    multiple
                    :options="province_options"/>
    </template>

    <div class="font-bold flex items-center mt-6 h-8">
      <span class="grow flex items-center">
        <BaseIcon :path="Icons.date"/>
        <span class="ml-2">Date range</span>
      </span>

      <BaseButton v-if="form.date_range?.length > 0"
                  @click="form.date_range = []"
                  :icon="Icons.clear"
                  color="warning"
                  outline
                  small/>
    </div>

    <Datepicker class="mt-3"
                :time-picker="false"
                show-now-button
                :enable-time-picker="false"
                range
                :dark="dark"
                placeholder="Select date range"
                v-model="form.date_range"/>

    <div class="font-bold flex items-center mt-6 h-8">
      <span class="grow flex items-center">
        <FormCheckRadioPicker class="mt-3"
                              name="Assigned to me only"
                              v-model="form.assigned_me"
                              type="checkbox"
                              :options="['Assigned to me only']"/>
      </span>
    </div>

    <div class="grid grid-cols-1 gap-3 mt-8">
      <BaseButton @click="apply" class="col-span-1" label="Apply" color="info"/>
      <BaseButton @click="resetForm" class="col-span-1" label="Reset" outline color="warning"/>
    </div>
  </CardBox>
</template>

<script setup>
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import PillTag from '@/Components/PillTag.vue'
import FormCheckRadioPicker from '@/Components/FormCheckRadioPicker.vue'
import {computed, nextTick, watch} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";

import {useStyleStore} from '@/Stores/style.js'

const styleStore = useStyleStore()

const dark = computed(() => styleStore.darkMode)

const props = defineProps({
  form: {
    type: Object,
    required: true
  }
})

watch(() => props.form.sector_ids, () => {
  props.form.report_type_ids = []
})

const province_options = computed(() => {
  return usePage().props.value.province_options
})

const status_options = computed(() => {
  return usePage().props.value.status_options
})

const sector_options = computed(() => {
  return usePage().props.value.sector_options
})

const report_type_options = computed(() => {
  if (props.form.sector_ids.length === 1) {
    const sector = sector_options.value.filter(item => `${item.id}` === props.form.sector_ids[0])
    if (sector.length > 0) {
      return sector[0].report_types
    }
  }
  return []
})

const emit = defineEmits([
  'apply'
])

const seeValue = () => {
  console.log(props.form)
}

const apply = () => {
  emit('apply')
}

const resetForm = () => {
  props.form.sector_ids = []
  props.form.report_type_ids = []
  props.form.status_ids = []
  props.form.date_range = []
  props.form.assigned_me = null

  emit('apply')
}

const filter_report_id = computed(() => {
  return route().params['id'];
})

const closeFilterOne = () => {
  emit('apply', {remove: ['id']})
}
</script>

<style>
  .vs__selected {
    @apply truncate;
  }
</style>
