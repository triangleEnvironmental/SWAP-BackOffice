<script setup>
import {ref, computed, onMounted} from 'vue'
import {Link} from '@inertiajs/inertia-vue3'
import {useStyleStore} from '@/Stores/style.js'
import {mdiMinus, mdiPlus} from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue'
import AsideMenuList from '@/Components/AsideMenuList.vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  isSubmenuList: Boolean
})

const emit = defineEmits(['menu-click'])

const styleStore = useStyleStore()

const isDropdownActive = ref(false)

const hasDropdown = computed(() => !!props.item.menu)

// Add itemHref
const itemHref = computed(() => {
  if (props.item.route) {
    if (props.item.routeParams) {
      return route(props.item.route, props.item.routeParams())
    }
    if (props.item.route.startsWith('/')) {
      return props.item.route
    } else {
      return route(props.item.route)
    }
  } else {
    return props.item.href ?? '#'
  }
})

// Add activeInactiveStyle
const activeInactiveStyle = computed(
  () => props.item.route && route().current(props.item.route)
    ? styleStore.asideMenuItemActiveStyle
    : styleStore.asideMenuItemInactiveStyle
)

const menuClick = event => {
  emit('menu-click', event, props.item)

  if (hasDropdown.value) {
    isDropdownActive.value = !isDropdownActive.value
  }
}

onMounted(() => {
  if (hasDropdown.value) {
    const childIsActive = props.item.menu.some((item) => {
      return item.route && route().current(item.route)
    })
    if (childIsActive) {
      isDropdownActive.value = true
    }
  }
})
</script>

<template>
  <li>
    <component
      :is="item.route ? Link : 'a'"
      :href="itemHref"
      :target="item.target ?? null"
      class="flex cursor-pointer dark:hover:bg-gray-700/50"
      :class="[ styleStore.asideMenuItemStyle, isSubmenuList ? 'p-3 text-sm' : 'py-2' ]"
      @click="menuClick"
    >
      <BaseIcon
        v-if="item.icon"
        :path="item.icon"
        class="flex-none"
        :class="activeInactiveStyle"
        w="w-12"
      />
      <span
        class="grow"
        :class="activeInactiveStyle"
      >{{ item.label }}</span>
      <BaseIcon
        v-if="hasDropdown"
        :path="isDropdownActive ? mdiMinus : mdiPlus"
        class="flex-none"
        :class="activeInactiveStyle"
        w="w-12"
      />
    </component>
    <AsideMenuList
      v-if="hasDropdown"
      :menu="item.menu"
      :class="[ styleStore.asideSubmenuListStyle, isDropdownActive ? 'block dark:bg-gray-800/50' : 'hidden' ]"
      is-submenu-list
    />
  </li>
</template>
