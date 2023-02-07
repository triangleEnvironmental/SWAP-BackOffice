import {
  mdiMonitor,
  mdiAccount,
  mdiCertificate,
  mdiFileDocument,
  mdiDumpTruck,
  mdiBank,
  mdiChatQuestion,
  mdiHomeGroup,
  mdiTagMultiple,
  mdiLabel,
  mdiMessageBadge,
  mdiFileImageMarker,
  mdiBell,
  mdiTimerCog,
  mdiAccountSearch,
  mdiViewList,
  mdiMapSearch,
  mdiInformation,
} from '@mdi/js'
import {usePage} from "@inertiajs/inertia-vue3";
import useHelper from "@/Composibles/useHelper";

// route is route name
export default [
  'General',
  [
    {
      route: 'dashboard',
      icon: mdiMonitor,
      label: 'Dashboard'
    },
    {
      icon: mdiFileImageMarker,
      label: 'Reports',
      needPermission: 'view-report',
      menu: [
        {
          label: 'List View',
          icon: mdiViewList,
          route: 'report.list',
        },
        {
          label: 'Map View',
          icon: mdiMapSearch,
          route: 'report.map',
        },
      ],
    },
    {
      route: 'faq.list',
      icon: mdiChatQuestion,
      label: 'FAQs',
      needPermission: 'view-faq',
    },
    {
      route: 'notification.list',
      icon: mdiBell,
      label: 'Citizen Notifications',
      needPermission: 'view-notification',
    },
  ],
  'Access Management',
  [
    {
      route: 'service-provider.list',
      icon: mdiDumpTruck,
      label: 'Service Providers',
      needPermission: 'view-service-provider',
    },
    {
      route: 'municipality.list',
      icon: mdiBank,
      label: 'Municipalities',
      needPermission: 'view-municipality',
    },
    {
      route: 'user.list',
      icon: mdiAccount,
      label: 'Users',
      needPermission: 'view-user',
    },
    // {
    //   route: 'citizen.list',
    //   icon: mdiAccountSearch,
    //   label: 'Citizens',
    //   needPermission: 'view-citizen',
    // },
  ],
  'Settings',
  [
    {
      route: 'sector.list',
      icon: mdiHomeGroup,
      label: 'Sectors',
      needPermission: 'view-sector',
    },
    {
      route: 'report-type.list',
      icon: mdiLabel,
      label: 'Report Types',
      needPermission: 'view-report-type',
    },
    {
      route: 'my-municipality.edit',
      routeParams: () => {
        const institution = usePage().props.value.my_institution
        return {id: institution?.id ?? 0}
      },
      icon: mdiBank,
      label: 'My Municipality',
      visibility: () => {
        if (!useHelper().can('view-own-municipality')) {
          return false;
        }
        const institution = usePage().props.value.my_institution
        return institution != null && institution.is_municipality
      }
    },
    {
      route: 'my-service-provider.edit',
      routeParams: () => {
        const institution = usePage().props.value.my_institution
        return {id: institution?.id ?? 0}
      },
      icon: mdiDumpTruck,
      label: 'My Service Provider',
      visibility: () => {
        if (!useHelper().can('view-own-service-provider')) {
          return false;
        }
        const institution = usePage().props.value.my_institution
        return institution != null && institution.is_service_provider
      }
    },
    {
      route: 'faq-category.list',
      icon: mdiTagMultiple,
      label: 'FAQ Categories',
      needPermission: 'view-faq-category',
    },
    {
      route: 'notification-preset.list',
      icon: mdiMessageBadge,
      label: 'Notification Presets',
      needPermission: 'view-notification-preset',
    },
    {
      route: 'visibility-config.list',
      icon: mdiTimerCog,
      label: 'Visibility Configuration',
      needPermission: 'configure-report-visibility-duration',
    },
  ],
  'Others',
  [
    {
      route: 'policy.show',
      label: 'Privacy policy',
      icon: mdiFileDocument,
    },
    {
      route: 'terms.show',
      label: 'Terms of service',
      icon: mdiCertificate,
    },
    {
      route: 'about.show',
      label: 'About',
      icon: mdiInformation,
    },
  ]
]
