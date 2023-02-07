<script setup>
import { computed, ref } from 'vue'
import { useStyleStore } from '@/Stores/style.js'
import { useLayoutStore } from '@/Stores/layout.js'
import {
  mdiForwardburger,
  mdiBackburger,
  mdiClose,
  mdiDotsVertical,
  mdiMenu,
  mdiClockOutline,
  mdiCloud,
  mdiCrop,
  mdiAccount,
  mdiCogOutline,
  mdiEmail,
  mdiLogout,
  mdiGithub,
  mdiThemeLightDark,
  mdiMonitorShimmer,
  mdiOpenInNew
} from '@mdi/js'
import NavBarItem from '@/Components/NavBarItem.vue'
import NavBarItemLabel from '@/Components/NavBarItemLabel.vue'
import NavBarMenu from '@/Components/NavBarMenu.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import UserAvatarCurrentUser from '@/Components/UserAvatarCurrentUser.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import NavBarSearch from '@/Components/NavBarSearch.vue'
import {Inertia} from "@inertiajs/inertia";
import {usePage} from "@inertiajs/inertia-vue3";

const userName = computed(() => usePage().props.value.user.name)

const styleStore = useStyleStore()

const toggleLightDark = () => {
  styleStore.setDarkMode()
}

const layoutStore = useLayoutStore()

const isAsideMobileExpanded = computed(() => layoutStore.isAsideMobileExpanded)

const menuToggleMobileIcon = computed(() => isAsideMobileExpanded.value ? mdiBackburger : mdiForwardburger)

const menuToggleMobile = () => layoutStore.asideMobileToggle()

const isMenuNavBarActive = ref(false)

const menuNavBarToggleIcon = computed(() => isMenuNavBarActive.value ? mdiClose : mdiDotsVertical)

const menuNavBarToggle = () => {
  isMenuNavBarActive.value = !isMenuNavBarActive.value
}

const menuOpenLg = () => {
  layoutStore.asideLgToggle(true)
}

const logout = () => {
  Inertia.post(route('logout'));
};

</script>

<template>
  <nav
    class="top-0 left-0 right-0 fixed flex bg-white h-14 border-b border-gray-100 z-30 w-screen
    transition-position xl:pl-60 lg:w-auto lg:items-stretch dark:bg-gray-900 dark:border-gray-800"
    :class="{'ml-60 lg:ml-0':isAsideMobileExpanded}"
  >
    <div class="flex-1 items-stretch flex h-14">
      <NavBarItem
        type="flex lg:hidden"
        @click.prevent="menuToggleMobile"
      >
        <BaseIcon
          :path="menuToggleMobileIcon"
          size="24"
        />
      </NavBarItem>
      <NavBarItem
        type="hidden lg:flex xl:hidden"
        @click.prevent="menuOpenLg"
      >
        <BaseIcon
          :path="mdiMenu"
          size="24"
        />
      </NavBarItem>
      <NavBarItem>
        <div id="search-bar"></div>
<!--        <NavBarSearch />-->
      </NavBarItem>
    </div>
    <div class="flex-none items-stretch flex h-14 lg:hidden">
      <NavBarItem @click.prevent="menuNavBarToggle">
        <BaseIcon
          :path="menuNavBarToggleIcon"
          size="24"
        />
      </NavBarItem>
    </div>
    <div
      class="absolute w-screen top-14 left-0 bg-white shadow
        lg:w-auto lg:items-stretch lg:flex lg:grow lg:static lg:border-b-0 lg:overflow-visible lg:shadow-none dark:bg-gray-900"
      :class="[isMenuNavBarActive ? 'block' : 'hidden']"
    >
      <div
        class="max-h-screen-menu overflow-y-auto lg:overflow-visible lg:flex lg:items-stretch lg:justify-end lg:ml-auto"
      >
<!--        <NavBarMenu has-divider>-->
<!--          <NavBarItemLabel-->
<!--            :icon="mdiMenu"-->
<!--            label="Sample menu"-->
<!--          />-->

<!--          <template #dropdown>-->
<!--            <NavBarItem>-->
<!--              <NavBarItemLabel-->
<!--                :icon="mdiClockOutline"-->
<!--                label="Item One"-->
<!--              />-->
<!--            </NavBarItem>-->
<!--            <NavBarItem>-->
<!--              <NavBarItemLabel-->
<!--                :icon="mdiCloud"-->
<!--                label="Item Two"-->
<!--              />-->
<!--            </NavBarItem>-->
<!--            <BaseDivider nav-bar />-->
<!--            <NavBarItem>-->
<!--              <NavBarItemLabel-->
<!--                :icon="mdiCrop"-->
<!--                label="Item Last"-->
<!--              />-->
<!--            </NavBarItem>-->
<!--          </template>-->
<!--        </NavBarMenu>-->
        <NavBarMenu has-divider>
          <NavBarItemLabel :label="userName">
            <UserAvatarCurrentUser class="w-6 h-6 mr-3 inline-flex" />
          </NavBarItemLabel>

          <template #dropdown>
            <NavBarItem :route-name="'profile.show'">
              <NavBarItemLabel
                :icon="mdiAccount"
                label="My Profile"
              />
            </NavBarItem>
<!--            <NavBarItem>-->
<!--              <NavBarItemLabel-->
<!--                :icon="mdiCogOutline"-->
<!--                label="Settings"-->
<!--              />-->
<!--            </NavBarItem>-->
<!--            <NavBarItem>-->
<!--              <NavBarItemLabel-->
<!--                :icon="mdiEmail"-->
<!--                label="Messages"-->
<!--              />-->
<!--            </NavBarItem>-->
            <BaseDivider nav-bar />
            <NavBarItem @click.prevent="logout">
              <NavBarItemLabel
                :icon="mdiLogout"
                label="Log Out"
              />
            </NavBarItem>
          </template>
        </NavBarMenu>
        <NavBarItem
          has-divider
          is-desktop-icon-only
          @click.prevent="toggleLightDark"
        >
          <NavBarItemLabel
            :icon="mdiThemeLightDark"
            label="Light/Dark"
            is-desktop-icon-only
          />
        </NavBarItem>
<!--        <NavBarItem-->
<!--          is-desktop-icon-only-->
<!--          @click="logout"-->
<!--        >-->
<!--          <NavBarItemLabel-->
<!--            :icon="mdiLogout"-->
<!--            label="Log out"-->
<!--            is-desktop-icon-only-->
<!--          />-->
<!--        </NavBarItem>-->
      </div>
    </div>
  </nav>
</template>
