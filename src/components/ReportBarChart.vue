<!-- eslint-disable -->
<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>

<script setup>
/* eslint-disable */

import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale
} from 'chart.js'
import { ref, watch } from 'vue'

// ثبت ماژول‌های ChartJS
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

// تعریف props به‌صورت معتبر
const props = defineProps({
  reportData: {
    type: Object,
    default: () => ({})
  }
})

// داده‌های نمودار
const chartData = ref({
  labels: [],
  datasets: []
})

// تنظیمات نمودار
const chartOptions = {
  responsive: true,
  plugins: {
    legend: { display: false }
  }
}

// واکنش به تغییر reportData
watch(
  () => props.reportData,
  (newData) => {
    chartData.value = {
      labels: Object.keys(newData || {}),
      datasets: [{
        label: 'تعداد قطعات',
        data: Object.values(newData || {}),
        backgroundColor: '#3b82f6'
      }]
    }
  },
  { immediate: true }
)
</script>

