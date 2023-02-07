<template>
  <GMapMap
    :center="center"
    :zoom="8"
    ref="mapRef"
    map-type-id="roadmap"
    style="width: 100%; height: 100%;"
    :options="mapOptions"
  >
    <MapGeoJson :geo-json="geoJson"
                :map="mapObject"/>
<!--    <GMapPolygon-->
<!--      :paths="polygons"-->
<!--    />-->
  </GMapMap>
</template>

<script setup>
import {ref, watch, inject, reactive, computed, onMounted, nextTick, onUpdated} from 'vue'
import useHelper from "@/Composibles/useHelper";
import {useStyleStore} from "@/Stores/style";
import {mapStyles} from "@/Components/map.config"
import onResume from "@/Composibles/onResume";
import MapGeoJson from "@/Components/MapGeoJson.vue"
const {getPoint, getLatLngBoundsFromGeoJson, getLatLngBoundsFromPoints, shortCount} = useHelper();

const polygons = computed(() => {
  let polygonList = []
  if (props.geoJson.type === 'MultiPolygon') {
    polygonList = props.geoJson.coordinates
  } else if (props.geoJson.type === 'Polygon') {
    polygonList = [props.geoJson.coordinates]
  }

  return polygonList.map(polygon => polygon[0].map(point => getPoint({coordinates: point})))
})

const props = defineProps({
  geoJson: {
    type: Object,
    required: true,
  },
  center: {
    type: Object,
    default: {
      lat: 11.562108,
      lng: 104.888535
    },
  }
})

const mapRef = ref(null)
const mapObject = ref(null)

watch(mapRef, googleMap => {
  if (googleMap) {
    googleMap.$mapPromise.then(map => {
      mapObject.value = map
      nextTick(() => {
        fitBound()
      })
    })
  }
})

onResume(() => {
  nextTick(() => {
    fitBound()
  })
})

const styleStore = useStyleStore()

const mapOptions = computed(() => ({
  clickableIcons: false,
  styles: styleStore.darkMode ? mapStyles.dark : mapStyles.light,
}))

const fitBound = () => {
  const latLngBound = getLatLngBoundsFromGeoJson(props.geoJson, 0)
  if (latLngBound) {
    mapObject.value?.fitBounds(latLngBound)
  }
}

defineExpose({
  fitBound
})
</script>

<style scoped>

</style>
