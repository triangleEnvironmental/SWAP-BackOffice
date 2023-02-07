<script setup>
import { mdiCog } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'
import IconRounded from '@/Components/IconRounded.vue'
import BaseButton from '@/Components/BaseButton.vue'

defineProps({
  icon: {
    type: String,
    default: null
  },
  title: {
    type: String,
    required: true
  },
  main: Boolean
})
</script>

<template>
  <section class="px-6 sm:px-0 mb-6 flex items-center justify-between">
    <div class="flex items-center justify-start">
      <IconRounded
        v-if="icon && main"
        :icon="icon"
        type="light"
        class="mr-3"
        bg
      />
      <BaseIcon
        v-else-if="icon"
        :path="icon"
        class="mr-3"
      />
      <h1 class="text-2xl">
        {{ title }}
      </h1>
    </div>
    <slot name="actions">
<!--      <BaseButton-->
<!--        :icon="mdiCog"-->
<!--        small-->
<!--      />-->
    </slot>
  </section>
</template>
