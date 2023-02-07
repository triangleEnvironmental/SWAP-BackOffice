<script setup>
import AsideMenuItem from '@/Components/AsideMenuItem.vue'
import {usePage} from "@inertiajs/inertia-vue3";

const props = defineProps({
  isSubmenuList: {
    type: Boolean,
    default: false
  },
  menu: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['menu-click'])

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}

</script>

<template>
  <ul>
    <AsideMenuItem
      v-for="(item, index) in menu"
      v-show="$helper.shouldDisplaySideMenuItem(item)"
      :key="index"
      :item="item"
      :is-submenu-list="isSubmenuList"
      @menu-click="menuClick"
    />
  </ul>
</template>
