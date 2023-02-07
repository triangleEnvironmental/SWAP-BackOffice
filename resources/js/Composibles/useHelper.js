import {usePage} from "@inertiajs/inertia-vue3";
import moment from "moment";

export default function useHelper() {
  const formatDate = (iso_date) => {
    try {
      return moment(iso_date).format('LL')
    } catch (e) {
      return null;
    }
  }

  const fromNow = (iso_date) => {
    try {
      return moment(iso_date).fromNow()
    } catch (e) {
      return null;
    }
  }

  const shortCount = (count) => {
    if (!count) {
      return '0'
    }
    if (count < 1000) {
      return `${count}`
    } else if (count < 100000) {
      const k = count / 1000
      if (k === Math.floor(k)) {
        return k.toFixed(0) + 'K'
      }
      return k.toFixed(1) + 'K'
    } else if (count < 100000000) {
      const m = count / 1000000
      if (m === Math.floor(m)) {
        return m.toFixed(0) + 'M'
      }
      return k.toFixed(1) + 'M'
    } else {
      return '99M+'
    }
  }

  const getLatLngBoundsFromPoints = (pointGeoJsons) => {
    return getLatLngBoundsFromGeoJson({
      type: 'LineString',
      coordinates: pointGeoJsons.map(e => e.coordinates),
    })
  }

  const getLatLngBoundsFromGeoJson = (geoJson, padding = 0.0002) => {
    let sw = {
      s: null,
      w: null,
    }
    let ne = {
      s: null,
      w: null,
    }

    let coordinates = [[[[]]]]

    if (geoJson.type === 'MultiPolygon') {
      padding = 0
      coordinates = geoJson.coordinates
    } else if (geoJson.type === 'Polygon') {
      padding = 0
      coordinates = [geoJson.coordinates]
    } else if (geoJson.type === 'LineString') {
      coordinates = [[geoJson.coordinates]]
    } else if (geoJson.type === 'Point') {
      coordinates = [[[geoJson.coordinates]]]
    }

    for (let polygon of coordinates) {
      for(let lineString of polygon) {
        for (let point of lineString) {
          if (sw.w == null || point[0] < sw.w) {
            sw.w = point[0]
          }
          if (sw.s == null || point[1] < sw.s) {
            sw.s = point[1]
          }
          if (ne.e == null || point[0] > ne.e) {
            ne.e = point[0]
          }
          if (ne.n == null || point[1] > ne.n) {
            ne.n = point[1]
          }
        }
      }
    }

    if (sw.w == null || sw.s == null || ne.e == null || ne.n == null) {
      return null;
    }

    sw.w -= padding
    sw.s -= padding
    ne.e += padding
    ne.n += padding

    if (!window.google) {
      return null
    }

    return new google.maps.LatLngBounds(
      new google.maps.LatLng(sw.s, sw.w),
      new google.maps.LatLng(ne.n, ne.e),
    )
  }

  const getPoint = (geojson) => ({
    lat: geojson.coordinates[1],
    lng: geojson.coordinates[0]
  })

  const can = (do_something) => {
    const permissions = usePage().props.value.permissions
    if (permissions) {
      return permissions.includes(do_something)
    }
    return false;
  }

  const selectOptions = (list, title, key = 'id', label = 'name_en') => {
    const options = list.map(e => ({id: e[key], label: e[label]}))
    options.unshift({id: null, label: title})
    return options
  }

  const arrayToObject = (list, key, value) => {
    const opt = {}
    if (list) {
      for (const item of list) {
        opt[item[key]] = item[value]
      }
    }
    return opt
  }

  const shouldDisplaySideMenuItem = (item) => {
    const checkVisibility = item.visibility ?? (() => true);
    const enoughPermission = item.needPermission ? usePage().props.value.permissions.includes(item.needPermission) : true
    return enoughPermission && checkVisibility()
  }

  const isSuperAdmin = () => {
    return usePage().props.value.user?.role_id === 1
  }

  return {
    formatDate,
    shortCount,
    can,
    getPoint,
    getLatLngBoundsFromGeoJson,
    getLatLngBoundsFromPoints,
    selectOptions,
    arrayToObject,
    fromNow,
    shouldDisplaySideMenuItem,
    isSuperAdmin,
  }
}
