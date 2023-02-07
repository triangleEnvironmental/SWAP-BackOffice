import '../css/main.css';
import '@vuepic/vue-datepicker/dist/main.css'
import 'vue-select/dist/vue-select.css';
import 'vue3-carousel/dist/carousel.css';
import 'vue3-photo-preview/dist/index.css';
import 'floating-vue/dist/style.css'
import "vue-toastification/dist/index.css";
import 'vue3-timeline/dist/style.css'
import '../css/variables.css';

import './bootstrap.js'
import {createPinia} from 'pinia'
import {useStyleStore} from '@/Stores/style.js'
import {useLayoutStore} from '@/Stores/layout.js'

import {darkModeKey, styleKey} from '@/config.js'

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import {InertiaProgress} from '@inertiajs/progress';
import {Inertia} from '@inertiajs/inertia'
import {Head} from '@inertiajs/inertia-vue3';
import MightNA from '@/Components/MightNA.vue'
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import useHelper from "@/Composibles/useHelper";
import VueGoogleMaps from '@fawmi/vue-google-maps'
import vue3PhotoPreview from 'vue3-photo-preview';
import vSelect from 'vue-select'
import {vue3Debounce} from 'vue-debounce'
import axios from 'axios'
import moment from 'moment'
import VueAxios from 'vue-axios'
import Icons from '@/Plugins/Icons/icons.js'
import PageNotification from "@/Components/PageNotification.vue"
import ConfirmModal from "@/Components/ConfirmModal.vue"
import FilterOption from "@/Components/FilterOption.vue"
import NavBarSearch from "@/Components/NavBarSearch.vue"
import FormValidationErrors from "@/Components/FormValidationErrors.vue"
import FloatingVue from 'floating-vue'
import Toast, {POSITION} from "vue-toastification";
import {plugin as Timeline} from 'vue3-timeline'

import Datepicker from '@vuepic/vue-datepicker';
import {registerPopState} from "@/Composibles/useRefreshNavigation";

vSelect.props.components.default = () => ({
  Deselect: {
    render: createElement => createElement('span', '❌'),
  },
  OpenIndicator: {
    render: createElement => createElement('span', '🔽'),
  },
});

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

const pinia = createPinia()

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({el, app, props, plugin}) {

    const vueApp = createApp({render: () => h(app, props)})
      .use(plugin)
      .use(pinia)
      .use(ZiggyVue, Ziggy)
      .use(VueGoogleMaps, {
        load: {
          key: 'AIzaSyApyUGfwBuXA3aUocbrKVx8nMC11MWSLbI',
        },
      })
      .use(VueAxios, axios)
      .use(vue3PhotoPreview)
      .use(FloatingVue)
      .use(Toast, {
        position: POSITION.BOTTOM_RIGHT
      })
      .use(Timeline)
      .directive('debounce', vue3Debounce({lock: true}))
      .component('Head', Head)
      .component('MightNA', MightNA)
      .component('PageNotification', PageNotification)
      .component('NavBarSearch', NavBarSearch)
      .component('ConfirmModal', ConfirmModal)
      .component('FormValidationErrors', FormValidationErrors)
      .component('FilterOption', FilterOption)
      .component('v-select', vSelect)
      .component('Datepicker', Datepicker)


    vueApp.provide('axios', vueApp.config.globalProperties.axios)
    vueApp.config.globalProperties.$helper = useHelper()
    vueApp.config.globalProperties.Icons = Icons
    vueApp.config.globalProperties.$moment = moment
    vueApp.config.globalProperties.window = window

    return vueApp.mount(el);
  },
});

InertiaProgress.init({color: '#4B5563'});
registerPopState();

const styleStore = useStyleStore(pinia)
const layoutStore = useLayoutStore(pinia)

/* App style */
styleStore.setStyle(localStorage[styleKey] ?? 'basic')

/* Dark mode */
if ((!localStorage[darkModeKey] && window.matchMedia('(prefers-color-scheme: dark)').matches) || localStorage[darkModeKey] === '1') {
  styleStore.setDarkMode(true)
}

/* Collapse mobile aside menu on route change */
Inertia.on('navigate', (event) => {
  layoutStore.asideMobileToggle(false)
  layoutStore.asideLgToggle(false)
})
