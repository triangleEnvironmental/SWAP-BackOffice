import {onMounted, onBeforeUnmount, onBeforeMount} from "vue";
import {Inertia} from "@inertiajs/inertia";

export function registerPopState() {
  window.popStateEvents = []
  window.popStateCallback = null
  window.addEventListener('popstate', (e) => {
    // console.log("On pop state", e)
    const isBackward = true
    if (isBackward) {
      window.popStateCallback = () => {
        for (const callback of window.popStateEvents) {
          callback()
        }
      }
    }
  });
}

export default function useRefreshNavigation(refreshProps) {
  async function onNavigate(event) {
    // console.log("on navigate")
    const options = {}
    if (refreshProps) {
      if (typeof refreshProps === 'string') {
        options['only'] = [refreshProps]
      } else if (Array.isArray(refreshProps)) {
        options['only'] = refreshProps
      }
    }
    await Inertia.reload(options)
  }

  onBeforeMount(() => {
    // console.log("Before mount")
    window.popStateEvents.push(onNavigate)
  })

  onMounted(() => {
    if (window.popStateCallback) {
      window.popStateCallback()
      window.popStateCallback = null
    }
  })

  onBeforeUnmount(() => {
    let find = window.popStateEvents.indexOf(onNavigate)
    if (find > -1) {
      window.popStateEvents.splice(find, 1)
    }
  })
}
