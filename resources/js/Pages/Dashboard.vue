<script setup>
import { Head } from '@inertiajs/inertia-vue3'
import { computed, ref, onMounted } from 'vue'
import { useMainStore } from '@/Stores/main'
import {
  mdiAccountMultiple,
  mdiCartOutline,
  mdiChartTimelineVariant,
  mdiFinance,
  mdiMonitorCellphone,
  mdiReload,
  mdiCogs,
  mdiAlert,
  mdiChartPie,
  mdiTooltipCheck,
  mdiFlag
} from '@mdi/js'
import * as chartConfig from '@/Components/Charts/chart.config.js'
import LineChart from '@/Components/Charts/LineChart.vue'
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleBar from '@/Components/SectionTitleBar.vue'
import SectionHeroBar from '@/Components/SectionHeroBar.vue'
import CardBoxWidget from '@/Components/CardBoxWidget.vue'
import CardBox from '@/Components/CardBox.vue'
import TableSampleClients from '@/Components/TableSampleClients.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBoxTransaction from '@/Components/CardBoxTransaction.vue'
import CardBoxClient from '@/Components/CardBoxClient.vue'
import SectionTitleBarSub from '@/Components/SectionTitleBarSub.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import {datasetObject} from '@/Components/Charts/chart.config'
import useRefreshNavigation from "@/Composibles/useRefreshNavigation";

useRefreshNavigation();

const props = defineProps([
  'report_count',
  'chart_data',
])

const titleStack = ref(['Backoffice', 'Dashboard'])

const chartData = computed(() => {
  let data = {
    labels: props.chart_data['7_days'].map(data => data.label),
    datasets: [
      datasetObject(
        'primary',
        props.chart_data['7_days'].map(data => data.count)
      ),
    ]
  }
  return data;
})

const mainStore = useMainStore()

const clientBarItems = computed(() => mainStore.clients.slice(0, 3))

</script>

<template>
  <Head title="Dashboard"/>

  <LayoutAuthenticated>
    <SectionTitleBar :title-stack="titleStack" />
<!--    <SectionHeroBar>Dashboard</SectionHeroBar>-->
    <SectionMain>
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <CardBoxWidget
          color="text-emerald-500"
          :icon="mdiTooltipCheck"
          :number="report_count.resolved"
          label="Resolved"
        />
        <CardBoxWidget
          color="text-yellow-500"
          :icon="mdiCogs"
          :number="report_count.in_progress"
          label="In Progress"
        />
        <CardBoxWidget
          color="text-red-500"
          :icon="mdiAlert"
          :number="report_count.open"
          label="Open"
        />
        <CardBoxWidget
          color="text-blue-500"
          :icon="mdiFlag"
          :number="report_count.moderation"
          label="Moderation"
        />
      </div>

      <SectionTitleBarSub
        :icon="mdiChartPie"
        title="Report Statistics"
      />

      <CardBox
        title="Reports made the last 7 days"
        :icon="mdiFinance"
        :header-icon="mdiReload"
        class="mb-6"
        :show-setting-button="false"
      >
        <div v-if="chartData">
          <line-chart
            :data="chartData"
            class="h-96"
          />
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
