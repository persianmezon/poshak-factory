<template>
  <div class="bg-white p-6 rounded shadow max-w-full overflow-hidden">
    <!-- نمایش مجموع -->
    <div class="text-center mb-4">
      <p class="text-gray-500 text-sm">مجموع کل</p>
      <p class="text-xl font-bold text-gray-800">{{ total }}</p>
    </div>

    <div class="flex flex-col md:flex-row items-center justify-center gap-6">
      <!-- نمودار دایره‌ای با ارتفاع محدود -->
      <div class="w-60 h-60">
        <Doughnut :data="chartData" :options="chartOptions" />
      </div>

      <!-- لیست درصدها -->
      <div class="space-y-3 text-sm text-gray-600">
        <div
          v-for="(item, index) in sections"
          :key="index"
          class="flex items-center gap-3"
        >
          <span
            class="inline-block w-3 h-3 rounded-full"
            :style="{ backgroundColor: item.color }"
          ></span>
          <span class="w-24">{{ item.label }}</span>
          <span class="font-bold">{{ item.value }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

export default {
  components: { Doughnut },
  data() {
    return {
      sections: [
        { label: 'دوخت', value: 45, color: '#22c55e' },
        { label: 'برش', value: 25, color: '#6366f1' },
        { label: 'انبار', value: 20, color: '#ef4444' },
        { label: 'دسته‌بندی‌نشده', value: 10, color: '#f59e0b' }
      ]
    }
  },
  computed: {
    total() {
      return this.sections.reduce((sum, s) => sum + s.value, 0)
    },
    chartData() {
      return {
        labels: this.sections.map(s => s.label),
        datasets: [
          {
            data: this.sections.map(s => s.value),
            backgroundColor: this.sections.map(s => s.color),
            borderWidth: 3, // نوار باریک‌تر
            cutout: '75%'   // فضای داخلی بیشتر
          }
        ]
      }
    },
    chartOptions() {
      return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
        }
      }
    }
  }
}
</script>
