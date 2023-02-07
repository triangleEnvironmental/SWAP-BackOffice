<script setup>
import {mdiThemeLightDark} from '@mdi/js'
import {useStyleStore} from '@/Stores/style.js'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButton from '@/Components/BaseButton.vue'
import RefreshDataButton from '@/Components/RefreshDataButton.vue'
import {Inertia} from "@inertiajs/inertia";

const styleStore = useStyleStore()

const props = defineProps({
  noFlex: {
    type: Boolean,
    default: true,
  },
  refreshableData: {
    type: [String, Array],
    default: null
  }
})

</script>

<template>
  <section
    class="bg-white border-t border-b border-gray-100 p-6 dark:bg-gray-900/70 dark:border-gray-900 dark:text-white"
  >
    <BaseLevel :no-flex="noFlex">
      <h1 class="text-3xl font-semibold leading-tight"
          :class="{'text-center': noFlex}">
        <slot/>
        <RefreshDataButton v-if="refreshableData"
                           :refreshable-data="refreshableData"
                           class="ml-2"/>
      </h1>
      <slot name="action">
      </slot>
    </BaseLevel>
  </section>
</template>
