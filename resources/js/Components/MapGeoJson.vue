<template></template>
<script setup>
import {computed, onBeforeUnmount, onMounted, watch} from "vue";

const props = defineProps({
  map: {
    type: Object,
  },
  geoJson: {
    type: Object,
    required: true,
  },
  style: {
    type: Object,
  },
})

const actionQueue = [];

const fixGeoJson = (geoJson) => {
  if (['FeatureCollection', 'Feature'].includes(props.geoJson.type)) {
    return props.geoJson
  } else {
    return {
      type: 'Feature',
      geometry: props.geoJson,
    }
  }
}

const executeMapComponentQueue = (map) => {
  for (const action of actionQueue) {
    action(map);
  }
}

const dispatch = (action) => {
  if (props.map) {
    action(props.map)
  } else {
    actionQueue.push(action)
  }
}

const onMapInitial = (map) => {
  executeMapComponentQueue(map)
}

watch(() => props.map, (mapObject) => {
  if (mapObject) {
    onMapInitial(mapObject)
  }
})

const removeGeoJson = (geoJson, map) => {
  const key = JSON.stringify(geoJson)
  if (featuresLookUp[key] !== undefined) {
    featuresLookUp[key].forEach(feature => {
      map.data.remove(feature);
    })
  }
}

const featuresLookUp = {}

const addGeoJson = (geoJson, map) => {
  const added = map.data.addGeoJson(fixGeoJson(geoJson))
  added.forEach(feature => {
    map.data.overrideStyle(feature, props.style ?? {})
  })
  featuresLookUp[JSON.stringify(geoJson)] = added
}

watch(() => props.geoJson, (newValue, oldValue) => {
  dispatch(map => {
    removeGeoJson(oldValue, map)
    addGeoJson(newValue, map)
  })
})

const events = [
  'click',
  'dblclick',
  'drag',
  'dragend',
  'dragstart',
  'mousedown',
  'mousemove',
  'mouseout',
  'mouseover',
  'mouseup',
  'rightclick',
]

onMounted(() => {
  if (props.map) {
    onMapInitial(props.map)
  }

  dispatch(map => {
    addGeoJson(props.geoJson, map)
  })
})

onBeforeUnmount(() => {
  dispatch(map => {
    removeGeoJson(props.geoJson, map)
  })
})
</script>
