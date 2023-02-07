<script setup>
import { mdiGithub } from '@mdi/js'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButton from '@/Components/BaseButton.vue'
import RefreshDataButton from '@/Components/RefreshDataButton.vue'

defineProps({
  titleStack: {
    type: Array,
    default: () => []
  },
  refreshableData: {
    type: [String, Array],
    default: null
  }
})
</script>

<template>
  <section class="p-6">
    <BaseLevel>
      <BaseLevel type="justify-start">
        <ul>
          <li
            v-for="(title, index) in titleStack"
            :key="index"
            class="section-title-bar-stack-item inline-block pr-3 text-2xl text-gray-500 dark:text-gray-400 last:pr-0 last:font-black last:text-black"
          >
            {{ title }}
          </li>
        </ul>
        <RefreshDataButton v-if="refreshableData"
                           :refreshable-data="refreshableData"
                           class="ml-2"/>
      </BaseLevel>

      <slot name="action">
      </slot>
    </BaseLevel>
  </section>
</template>
