<template>
  <template
    v-if="inline"
    v-for="type in flashTypes">
    <NotificationBar
      v-if="typeof $page.props.jetstream.flash === 'object' && $page.props.jetstream.flash[type]"
      :inside-section="insideSection"
      :color="type"
      :icon="mdiInformation"
      @dismiss="onDismissNotification(type)"
    >
      <span v-html="$page.props.jetstream.flash[type]"></span>
    </NotificationBar>
  </template>
</template>

<script setup>
import NotificationBar from '@/Components/NotificationBar.vue'
import {
  mdiInformation
} from '@mdi/js'
import {ref, watch} from 'vue'
import {usePage} from "@inertiajs/inertia-vue3";
import {useToast} from "vue-toastification";

const flashTypes = ['success', 'info', 'warning', 'danger']

const toast = useToast()

const props = defineProps({
  insideSection: {
    type: Boolean,
    default: true,
  },
  inline: {
    type: Boolean,
    default: false,
  }
})

watch(() => usePage().props.value.jetstream.flash, (flash) => {
  if (!props.inline) {
    if (typeof flash === 'object') {
      for (const type of flashTypes) {
        if (flash[type]) {
          switch (type) {
            case 'danger':
              toast.error(flash[type])
              break
            default:
              toast[type](flash[type])
          }
        }
      }
    }
  }
})

const onDismissNotification = (type) => {
  const flash = usePage().props.value.jetstream?.flash
  if (typeof flash === "object") {
    delete flash[type]
  }
}
</script>
