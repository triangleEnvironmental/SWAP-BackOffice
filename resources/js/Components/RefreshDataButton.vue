<template>
  <BaseButton class="ml-2"
              :icon="Icons.reload"
              @click="refreshData"/>
</template>

<script setup>
import BaseButton from '@/Components/BaseButton.vue'
import {Inertia} from "@inertiajs/inertia";

const props = defineProps({
  refreshableData: {
    type: [String, Array],
    required: true,
  }
})

const refreshData = () => {
  Inertia.reload({
    only: typeof props.refreshableData === 'string' ? [props.refreshableData] : props.refreshableData,
  })
}
</script>

<style scoped>

</style>
