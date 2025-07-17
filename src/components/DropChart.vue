<template>
  <div>
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'DropChart',
  components: { Bar },
  props: ['batches'],
  computed: {
    chartData() {
      const grouped = {}

      this.batches.forEach(batch => {
        if (!grouped[batch.workerName]) grouped[batch.workerName] = []
        grouped[batch.workerName].push(batch.dropPercent)
      })

      const labels = Object.keys(grouped)
      const values = labels.map(name => {
        const drops = grouped[name]
        const avg = drops.reduce((a, b) => a + b, 0) / drops.length
        return parseFloat(avg.toFixed(1))
      })

      return {
        labels,
        datasets: [
          {
            label: 'میانگین افت عملکرد (%)',
            data: values,
            backgroundColor: '#f87171'
          }
        ]
      }
    },
    chartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: {
            display: true
          },
          title: {
            display: true,
            text: 'نمودار افت عملکرد خیاط‌ها'
          }
        }
      }
    }
  }
}
</script>
