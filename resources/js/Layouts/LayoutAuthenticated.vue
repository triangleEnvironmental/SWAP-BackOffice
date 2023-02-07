<script setup>
import { computed } from 'vue'
import { useLayoutStore } from '@/Stores/layout.js'
import { useStyleStore } from '@/Stores/style.js'
import menu from '@/menu.js'
import NavBar from '@/Components/NavBar.vue'
import AsideMenu from '@/Components/AsideMenu.vue'
import FooterBar from '@/Components/FooterBar.vue'
import OverlayLayer from '@/Components/OverlayLayer.vue'

const props = defineProps({
  layoutClass: {
    default: null,
  }
})

const styleStore = useStyleStore()

const layoutStore = useLayoutStore()

const isAsideLgActive = computed(() => layoutStore.isAsideLgActive)

const overlayClick = () => {
  layoutStore.asideLgToggle(false)
}
</script>

<template>
  <div :class="{ 'dark': styleStore.darkMode, 'overflow-hidden lg:overflow-visible': layoutStore.isAsideMobileExpanded }">
    <div
      :class="[styleStore.appStyle, { 'ml-60 lg:ml-0': layoutStore.isAsideMobileExpanded }]"
      class="layout-authenticated pt-14 xl:pl-60 w-screen transition-position lg:w-auto"
    >
      <NavBar />
      <AsideMenu :menu="menu" />
      <div class="layout-content" :class="layoutClass">
        <slot />
      </div>
      <FooterBar />
      <OverlayLayer
        v-show="isAsideLgActive"
        z-index="z-30"
        @overlay-click="overlayClick"
      />
    </div>
  </div>
</template>

<style scoped>
.layout-content {
  min-height: calc(100vh - 96px);
  height: calc(100vh - 96px);
  overflow-y: auto;
}
</style>
