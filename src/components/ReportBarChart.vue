<script>
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'ReportBarChart',
  components: { Bar },
  props: {
    reportData: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      chartOptions: {
        responsive: true,
        plugins: {
          legend: { display: false }
        }
      }
    }
  },
  computed: {
    chartData() {
      if (!this.reportData || Object.keys(this.reportData).length === 0) return null
      return {
        labels: Object.keys(this.reportData),
        datasets: [{
          label: 'تعداد قطعات',
          data: Object.values(this.reportData),
          backgroundColor: '#3b82f6'
        }]
      }
    }
  }
}
</script>

<template>
  <div v-if="chartData">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
  <div v-else class="text-center text-sm text-gray-500 py-4">
    در حال آماده‌سازی نمودار...
  </div>
</template>
