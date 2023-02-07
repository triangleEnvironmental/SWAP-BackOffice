<script setup>
import {useStyleStore} from '@/Stores/style.js'
import {useLayoutStore} from '@/Stores/layout.js'
import {mdiMenu} from '@mdi/js'
import AsideMenuList from '@/Components/AsideMenuList.vue'
import NavBarItem from '@/Components/NavBarItem.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import {computed} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";
import useHelper from "@/Composibles/useHelper";

const props = defineProps({
  menu: {
    type: Array,
    default: () => []
  }
})

const helper = useHelper();

const styleStore = useStyleStore()

const layoutStore = useLayoutStore()

const title = computed(() => {
  const myInstitution = usePage().props.value.my_institution
  if (myInstitution) {
    return `<b>${myInstitution.name_en}</b>`
  }
  return '<span>SWAP</span> <b>Backoffice</b>';
})

const isNextGroupVisible = (index) => {
  if (props.menu[index + 1] === undefined || typeof props.menu[index + 1] === 'string') {
    return true;
  }
  if (Array.isArray(props.menu[index + 1])) {
    return props.menu[index + 1].some(item => helper.shouldDisplaySideMenuItem(item))
  }
  return true
}

const menuClick = () => {
  //
}
</script>

<template>
  <aside
    id="aside"
    class="w-60 fixed top-0 z-40 h-screen transition-position lg:left-0 overflow-y-auto
    dark:border-r dark:border-gray-800 dark:bg-gray-900 xl:dark:bg-gray-900/70"
    :class="[ styleStore.asideStyle, layoutStore.isAsideMobileExpanded ? 'left-0' : '-left-60', layoutStore.isAsideLgActive ? 'block' : 'lg:hidden xl:block' ]"
  >
    <div
      class="flex flex-row w-full flex-1 h-14 items-center dark:bg-transparent"
      :class="[ styleStore.asideBrandStyle ]"
    >
      <NavBarItem
        type="hidden lg:flex xl:hidden"
        :active-color="styleStore.asideMenuCloseLgStyle"
        active
        @click="layoutStore.asideLgToggle(false)"
      >
        <BaseIcon
          :path="mdiMenu"
          class="cursor-pointer"
          size="24"
        />
      </NavBarItem>
      <div class="flex-1 px-3" v-html="title">

      </div>
    </div>
    <div>
      <template v-for="(menuGroup, index) in menu">
        <p
          v-if="typeof menuGroup === 'string'"
          v-show="isNextGroupVisible(index)"
          :key="`a-${index}`"
          class="p-3 text-xs uppercase"
          :class="styleStore.asideMenuLabelStyle"
        >
          {{ menuGroup }}
        </p>
        <AsideMenuList
          v-else
          :key="`b-${index}`"
          :menu="menuGroup"
          @menu-click="menuClick"
        />
      </template>
    </div>
  </aside>
</template>
