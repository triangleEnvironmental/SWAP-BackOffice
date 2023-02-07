<script>
import {buildComponent} from "@fawmi/vue-google-maps/src/main"
import getHtmlMarkerClass from "@/Plugins/Map/HTMLMapMarker";

const props = {
  animation: {
    twoWay: true,
    type: Number,
  },
  attribution: {
    type: Object,
  },
  clickable: {
    type: Boolean,
    twoWay: true,
    default: true,
  },
  cursor: {
    type: String,
    twoWay: true,
  },
  draggable: {
    type: Boolean,
    twoWay: true,
    default: false,
  },
  html: {
    type: String,
    default: "<div style='background-color: red; color: white;'>DEFINEMARKER</div>",
  },
  label: {},
  opacity: {
    type: Number,
    default: 1,
  },
  options: {
    type: Object,
  },
  place: {
    type: Object,
  },
  position: {
    type: Object,
    twoWay: true,
  },
  shape: {
    type: Object,
    twoWay: true,
  },
  title: {
    type: String,
    twoWay: true,
  },
  zIndex: {
    type: Number,
    twoWay: true,
  },
  visible: {
    twoWay: true,
    default: true,
  },
}

const events = [
  'click',
  'rightclick',
  'dblclick',
  'drag',
  'dragstart',
  'dragend',
  'mouseup',
  'mousedown',
  'mouseover',
  'mouseout',
]

export default {
  // setup(props, { slots }) {
  //   return {
  //     html: "<div>HIHIHI</div>",
  //   }
  // },
  ...buildComponent({
    mappedProps: {
      ...props,
    },
    events,
    name: 'marker',
    ctr: getHtmlMarkerClass,

    emits: events,
    unmounted() {
      if (!this.$markerObject) {
        return
      }

      if (this.$clusterObject) {
        // Repaint will be performed in `updated()` of cluster
        this.$clusterObject.removeMarker(this.$markerObject, true)
      } else {
        this.$markerObject.setMap(null)
      }
    },

    afterCreate(inst) {
      events.forEach((event) => {
        inst.addListener(event, (payload) => {
          this.$emit(event, payload)
        });
      })
    },
  }),
}
</script>
