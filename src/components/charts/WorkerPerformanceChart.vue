<template>
  <div>
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement)

const chartData = {
  labels: ['01.02', '05.02', '10.02', '15.02', '20.02', '25.02'],
  datasets: [
    {
      label: 'انجام‌شده',
      data: [12, 19, 23, 25, 21, 24],
      fill: false,
      borderColor: '#4CAF50',
      backgroundColor: '#4CAF50',
      tension: 0.3,
    },
    {
      label: 'مانده',
      data: [5, 9, 14, 11, 13, 10],
      fill: false,
      borderColor: '#F87171',
      backgroundColor: '#F87171',
      tension: 0.3,
    }
  ]
}

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      labels: {
        font: {
          family: 'Vazirmatn'
        }
      }
    }
  },
  scales: {
    y: {
      ticks: {
        stepSize: 5
      }
    }
  }
}
</script>
