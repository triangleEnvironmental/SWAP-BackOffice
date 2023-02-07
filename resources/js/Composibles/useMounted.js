import {onMounted, nextTick, ref} from "vue";

export default function () {
  const isMounted = ref(false)

  onMounted(async () => {
    await nextTick(() => {
      isMounted.value = true
    })
  })

  return {
    isMounted,
  }
}
