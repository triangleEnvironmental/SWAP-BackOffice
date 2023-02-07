<script setup>
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue';
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionMain from '@/Components/SectionMain.vue'
import NavBarSearch from '@/Components/NavBarSearch.vue'
import CustomMarker from '@/Components/CustomMarker.vue'
import CardBox from '@/Components/CardBox.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormControl from '@/Components/FormControl.vue'
import ReportFilter from '@/Components/ReportFilter.vue'
import ReportPreviewModal from '@/Components/ReportPreviewModal.vue'
import useMounted from "@/Composibles/useMounted";
import {debounce} from "vue-debounce";
import {ref, watch, inject, reactive, computed, onMounted} from 'vue'
import useHelper from "@/Composibles/useHelper";
import {useStyleStore} from "@/Stores/style";
import {mapStyles} from "@/Components/map.config"
import useFilterForm from "@/Composibles/useFilterForm";
import {mdiFilterMenuOutline} from "@mdi/js"

// const {filterForm, applyFilter, getAppliedFilterOptions} = useFilterForm({
//   status_id: null,
//   keyword: null
// })

const {filterForm, applyFilter, getAppliedFilterOptions, applyFilterWithOption} = useFilterForm({
  sector_ids: [],
  report_type_ids: [],
  status_ids: [],
  date_range: [],
  area_ids: [],
  assigned_me: false,
  keyword: null
})

const axios = inject('axios')

const props = defineProps([
  'status_options',
])

const previewReportModalOpen = ref(false)
const currentPreviewReport = ref(null)

const reports = ref([])

const mapRef = ref(null)
const mapObject = ref(null)

watch(mapRef, googleMap => {
  if (googleMap) {
    googleMap.$mapPromise.then(map => {
      mapObject.value = map
    })
  }
})

let willZoomFit = true

const center = ref({lat: 12.56218211634905, lng: 105.13587292512317})

const titleStack = ref(['Backoffice', 'Reports Map'])

const styleStore = useStyleStore()

const mapOptions = computed(() => ({
  clickableIcons: false,
  styles: styleStore.darkMode ? mapStyles.dark : mapStyles.light,
}))

const fitBound = () => {
  const latLngBound = getLatLngBoundsFromPoints(reports.value.map(e => e.location))
  if (latLngBound) {
    if (reports.value.length === 1) {
      mapObject.value?.fitBounds(latLngBound, 500)
    } else {
      mapObject.value?.fitBounds(latLngBound, 0)
    }
  }
}

const updateQuery = async () => {
  const response = await axios.get(route('report.map.query'), {
    params: {
      ...currentBound.value,
      zoom: mapObject.value?.getZoom(),
      id: route().params.id,
      ...getAppliedFilterOptions()
    }
  })

  reports.value = response.data.data

  if (willZoomFit) {
    fitBound();
    willZoomFit = false;
  }
}

const currentBound = ref()

const fetchDataAfterBoundsChanged = debounce((newBound = null) => {
  if (newBound) {
    const ne = newBound.getNorthEast()
    const sw = newBound.getSouthWest()
    currentBound.value = {
      minLng: sw.lng(),
      maxLng: ne.lng(),
      minLat: sw.lat(),
      maxLat: ne.lat(),
    }
  }

  updateQuery();
}, '400ms')

const boundsChanged = (event) => {
  // Fetch clustered data again with debounce
  fetchDataAfterBoundsChanged(event)
}

const getHtmlMarker = (marker) => {
  if (marker.is_cluster) {
    const color = marker.count < 10 ? '#04559d' : (marker.count < 100 ? '#fa821a' : '#e23d31')
    return `<div style="transform: translate(-50%, -50%); background-color: ${color}; box-shadow: 0 0 0 5px ${color}73; color: white; font-weight: bolder; border-radius: 15px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center">
                <span>${shortCount(marker.count)}</span>
            </div>`
  } else {
    return `<div style="transform: translate(-50%, -100%);">
              <svg style="width: 36px; height: 40px;" clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 250 310" xmlns="http://www.w3.org/2000/svg">
                <g fill-rule="nonzero" transform="translate(-50.8944 -20.6867)">
                  <path d="m61.74 88.77c22.42-45.94 76.06-74.27 126.64-66.93 32.01 4.65 62.08 21.97 81.92 47.56 13.76 17.91 22 40.04 22.75 62.65.81 18.76-4.4 37.19-11.55 54.37-11.44 26.94-27.68 51.55-45.36 74.78-13.25 17.25-27.39 33.81-42.41 49.53-3.9 3.83-7.34 8.43-12.52 10.63-7.18 3.33-16.02 2.37-22.41-2.27-5.33-4.65-10-10.03-14.89-15.13-16.34-17.75-31.74-36.4-45.8-56.01-18.38-26-35.25-53.87-43.58-84.86-6.74-24.58-3.98-51.47 7.21-74.32m96.75-49.99c-22.57 3.13-44.05 13.55-60.52 29.29-16.27 15.41-27.3 36.52-29.44 58.92-2.31 20.18 3.92 40.14 12.19 58.3 8.09 17.49 18.28 33.92 29.38 49.64 17.46 24.54 36.91 47.64 57.84 69.28 1.99 2.57 6.17 2.53 8.16-.02 21.75-22.49 41.93-46.53 59.88-72.16 15.59-22.79 30.1-47.03 37.09-73.98 5.26-19.99 3.09-41.69-5.68-60.37-18.74-40.13-65.11-65.01-108.9-58.9z" fill="#f2f2f2" />
                  <path d="m163.49 93.71c11.36-2.18 23.56.13 33.21 6.54 11.12 7.18 18.77 19.48 20.18 32.65 1.49 12.31-2.39 25.14-10.49 34.52-8.41 9.99-21.32 15.97-34.39 15.88-13.55.1-26.92-6.39-35.32-17.02-8.79-10.79-11.97-25.78-8.51-39.24 4.02-16.71 18.42-30.27 35.32-33.33z" fill="#f2f2f2" />
                  <path d="m158.49 38.78c43.79-6.11 90.16 18.77 108.9 58.9 8.77 18.68 10.94 40.38 5.68 60.37-6.99 26.95-21.5 51.19-37.09 73.98-17.95 25.63-38.13 49.67-59.88 72.16-1.99 2.55-6.17 2.59-8.16.02-20.93-21.64-40.38-44.74-57.84-69.28-11.1-15.72-21.29-32.15-29.38-49.64-8.27-18.16-14.5-38.12-12.19-58.3 2.14-22.4 13.17-43.51 29.44-58.92 16.47-15.74 37.95-26.16 60.52-29.29m-74.04 66.67c-6.7 16.13-7.92 34.46-3.2 51.29 6.15 22.76 18.06 43.45 30.93 63.01 17.72 26.31 38.03 50.81 59.82 73.84 26.52-28.15 51.27-58.27 70.97-91.64 9.39-16.38 17.83-33.71 21.37-52.4 4.21-22.25-2.29-45.89-16.14-63.65-14.85-19.22-37.21-32.31-61.1-36.44-41.48-7.24-86.49 16.96-102.65 55.99z" fill="#40396e" />
                  <path fill="${marker.status?.color ?? 'red'}" d="m84.45 105.45c16.16-39.03 61.17-63.23 102.65-55.99 23.89 4.13 46.25 17.22 61.1 36.44 13.85 17.76 20.35 41.4 16.14 63.65-3.54 18.69-11.98 36.02-21.37 52.4-19.7 33.37-44.45 63.49-70.97 91.64-21.79-23.03-42.1-47.53-59.82-73.84-12.87-19.56-24.78-40.25-30.93-63.01-4.72-16.83-3.5-35.16 3.2-51.29m79.04-11.74c-16.9 3.06-31.3 16.62-35.32 33.33-3.46 13.46-.28 28.45 8.51 39.24 8.4 10.63 21.77 17.12 35.32 17.02 13.07.09 25.98-5.89 34.39-15.88 8.1-9.38 11.98-22.21 10.49-34.52-1.41-13.17-9.06-25.47-20.18-32.65-9.65-6.41-21.85-8.72-33.21-6.54z" />
                  <path d="m270.3 69.4c1.95.16 2.8 2.11 4.01 3.34 15.03 17.97 24.33 40.75 25.53 64.2 1.11 17.08-2.89 34.05-8.9 49.93-11.9 30.82-30.44 58.55-50.53 84.59-14.25 18.25-29.45 35.79-45.76 52.25-5.97 6.08-15.54 8.17-23.51 5.14-5.1-1.76-8.94-5.75-12.34-9.76 6.39 4.64 15.23 5.6 22.41 2.27 5.18-2.2 8.62-6.8 12.52-10.63 15.02-15.72 29.16-32.28 42.41-49.53 17.68-23.23 33.92-47.84 45.36-74.78 7.15-17.18 12.36-35.61 11.55-54.37-.75-22.61-8.99-44.74-22.75-62.65z" fill-opacity=".35" />
                </g>
              </svg>
            </div>`
  }
}

const {getPoint, getLatLngBoundsFromGeoJson, getLatLngBoundsFromPoints, shortCount} = useHelper();

const onClickMarker = (report) => {
  // Click on cluster
  if (report.is_cluster) {
    const latLngBound = getLatLngBoundsFromGeoJson(report.bounding)
    if (latLngBound) {
      mapObject.value?.fitBounds(latLngBound, 0)
    }
  } else { // Click on report
    currentPreviewReport.value = report
    previewReportModalOpen.value = true
    // const latLng = new google.maps.LatLng(report.location.coordinates[1], report.location.coordinates[0])
    // mapObject.value?.panTo(latLng)
  }
}

const {isMounted} = useMounted()

</script>

<template>
  <Head title="Reports"/>

  <NavBarSearch v-model="filterForm.keyword"
                @submit="applyFilter"/>

  <ReportPreviewModal v-if="currentPreviewReport"
                      :can-view-on-map="false"
                      :report="currentPreviewReport"
                      v-model="previewReportModalOpen">
  </ReportPreviewModal>

  <!--  <Teleport v-if="isMounted" to="#search-bar">-->
  <!--    <NavBarSearch placeholder="Search... (Ctrl + K)"/>-->
  <!--  </Teleport>-->

  <LayoutAuthenticated layout-class="flex flex-col">
    <SectionTitleBar class="grow-0" :title-stack="titleStack">
      <template v-slot:action>
        <BaseButton color="info"
                    :route-name="route('report.list', route().params)"
                    :icon="Icons.list"
                    label="View List"/>
      </template>
    </SectionTitleBar>

    <PageNotification :inside-section="false"/>

    <div class="map-container grow">
      <div class="grid gap-2 grid-cols-12">
        <div class="col-span-12 md:col-span-4 lg:col-span-3 order-1 md:order-2 md:mr-2">
          <ReportFilter :form="filterForm"
                        @apply="applyFilterWithOption"/>
        </div>
        <GMapMap
          class="h-screen mb-6 col-span-12 md:col-span-8 lg:col-span-9 order-2 md:order-1"
          :center="center"
          :zoom="8"
          ref="mapRef"
          map-type-id="roadmap"
          @bounds_changed="boundsChanged"
          style="width: 100%;"
          :options="mapOptions"
        >
          <CustomMarker
            v-for="(m, index) in reports"
            :key="JSON.stringify(m)"
            :label="m.count"
            :position="getPoint(m.location)"
            :clickable="true"
            :draggable="false"
            :html="getHtmlMarker(m)"
            @click="onClickMarker(m)"
          >
          </CustomMarker>
        </GMapMap>
      </div>
    </div>

  </LayoutAuthenticated>
</template>

<style>
.map-container {
  @apply md:flex;
}

.vue-map-container, .vue-map {
  @apply grow flex flex-col;
}

.vue-map-container {
  @apply h-screen md:h-full;
}
</style>

