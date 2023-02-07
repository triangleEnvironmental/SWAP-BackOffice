import {onMounted, onUpdated} from "vue";

export default function onResume(callback) {
  let counter = 0
  onMounted(() => {
    callback()
    counter++
  })

  onUpdated(() => {
    if (counter % 2 === 0) {
      callback()
    }
    counter++
  })
}
