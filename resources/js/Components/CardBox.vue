<script setup>
import {mdiClose, mdiCog} from '@mdi/js'
import { computed } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
  title: {
    type: String,
    default: null
  },
  showSettingButton: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: String,
    default: null
  },
  headerIcon: {
    type: String,
    default: mdiClose
  },
  rounded: {
    type: String,
    default: 'md:rounded-2xl'
  },
  hasTable: Boolean,
  empty: Boolean,
  form: Boolean,
  hoverable: Boolean,
  settingTooltip: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['header-icon-click', 'submit'])

const is = computed(() => props.form ? 'form' : 'div')

const componentClass = computed(() => {
  const base = [
    props.rounded,
    props.modal ? 'dark:bg-gray-900' : 'dark:bg-gray-900/70'
  ]

  if (props.hoverable) {
    base.push('hover:shadow-lg transition-shadow duration-500')
  }

  return base
})

const computedHeaderIcon = computed(() => props.headerIcon ?? mdiCog)

const headerIconClick = () => {
  emit('header-icon-click')
}

const submit = e => {
  emit('submit', e)
}
</script>

<template>
  <component
    :is="is"
    :class="componentClass"
    class="card-box bg-white border border-gray-100 dark:border-gray-800"
    @submit="submit"
  >
    <header
      v-if="title"
      class="card-box-header flex items-stretch border-b border-gray-100 dark:border-gray-800"
    >
      <p
        class="flex items-center py-3 grow font-bold"
        :class="[ icon ? 'px-4' : 'px-6' ]"
      >
        <BaseIcon
          v-if="icon"
          :path="icon"
          class="mr-3"
        />
        {{ title }}
      </p>
      <a
        v-if="showSettingButton && computedHeaderIcon"
        v-tooltip="settingTooltip"
        href="#"
        class="flex items-center py-3 px-4 justify-center ring-blue-700 focus:ring"
        aria-label="more options"
        @click.prevent="headerIconClick"
      >
        <BaseIcon :path="computedHeaderIcon"/>
      </a>
    </header>
    <div
      v-if="empty"
      class="card-box-body text-center py-24 text-gray-500 dark:text-gray-400"
    >
      <p>Nothing's here…</p>
    </div>
    <div
      v-else
      class="card-box-body"
      :class="{'p-6':!hasTable}"
    >
      <slot />
    </div>

    <slot name="actions"></slot>

  </component>
</template>

<style scoped>
.card-box-modal .card-box {
  @apply flex flex-col;
}

.card-box-modal .card-box-body {
  flex-grow: 1;
  @apply overflow-y-auto;
}
</style>
