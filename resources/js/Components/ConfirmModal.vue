<template>
  <CardBoxModal
    v-model="modalOpen"
    :title="titleRef"
    :button-label="buttonLabelRef"
    @confirm="okay"
    @cancel="close"
    has-cancel
  >
    {{ contentRef }}
  </CardBoxModal>
</template>

<script setup>
import CardBoxModal from "@/Components/CardBoxModal.vue"
import {ref} from "vue";

const props = defineProps({
  clickable: {
    type: Boolean,
    default: true
  }
})

const modalOpen = ref(false)
const titleRef = ref('Alert')
const buttonLabelRef = ref('Confirm')
const contentRef = ref('For the reason of this alert, do you agree?')

const generator = ref(null)

const close = () => {
  modalOpen.value = false
  if (generator.value) {
    generator.value.next(false)
  }
}

const okay = () => {
  modalOpen.value = false
  if (generator.value) {
    generator.value.next(true)
  }
}

const openModal = async ({title, content, buttonLabel} = {}) => {
  return new Promise((resolve, reject) => {
    if (buttonLabel) {
      buttonLabelRef.value = buttonLabel
    }
    if (title) {
      titleRef.value = title
    }
    if (content) {
      contentRef.value = content
    }

    modalOpen.value = true
    generator.value = (function* () {
      resolve(yield)
    })()
    generator.value.next()
  })
}

defineExpose({
  openModal
})
</script>

<style scoped>

</style>
