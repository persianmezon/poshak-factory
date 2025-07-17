<template>
<AppLayout>
  <div id="dashboard-content">

    <!-- دکمه PDF -->
    <div class="flex justify-end mb-6">
      <button @click="generatePDF" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md text-lg">
        📄 دانلود گزارش PDF
      </button>
    </div>

    <h1 class="text-3xl font-extrabold text-gray-800 mb-8">داشبورد کارخانه</h1>

    <!-- کارت‌های آماری -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
      <div class="p-6 bg-white rounded-2xl shadow-xl">
        <h2 class="text-lg text-gray-700 font-bold mb-2">تولید کل این ماه</h2>
        <p class="text-4xl font-extrabold text-green-600">{{ totalPieces }}</p>
      </div>

      <div class="p-6 bg-white rounded-2xl shadow-xl">
        <h2 class="text-lg text-gray-700 font-bold mb-2">تعداد هشدارهای این ماه</h2>
        <p class="text-4xl font-extrabold text-red-600">{{ totalWarnings }}</p>
      </div>

      <div class="p-6 bg-white rounded-2xl shadow-xl">
        <h2 class="text-lg text-gray-700 font-bold mb-2">درصد افت این ماه</h2>
        <p class="text-4xl font-extrabold text-yellow-500">{{ performanceDrop.toFixed(1) }}%</p>
      </div>

      <div class="p-6 rounded-2xl shadow-xl" :class="healthColorClass">
        <h2 class="text-lg text-gray-700 font-bold mb-2">شاخص سلامت کارخانه</h2>
        <p class="text-4xl font-extrabold flex items-center justify-center">
          {{ healthIndex.toFixed(1) }}/100
          <span class="ml-2 text-5xl">{{ healthEmoji }}</span>
        </p>
      </div>
    </div>

        <div class="p-4">
      <h2 class="text-2xl font-bold mb-4">👋 خوش آمدید</h2>
      <p class="text-lg">شما با نقش <span class="text-primary font-semibold">{{ role }}</span> وارد شده‌اید.</p>
    </div>

    <!-- چارت تولید -->
    <div class="p-6 bg-white rounded-2xl shadow-xl mb-10">
      <h2 class="text-xl font-bold text-gray-700 mb-4">تولید ۶ ماه اخیر</h2>
      <BarChart :chart-data="chartData" :chart-options="chartOptions" />
    </div>

    <!-- چارت افت عملکرد -->
    <div class="p-6 bg-white rounded-2xl shadow-xl">
      <h2 class="text-xl font-bold text-gray-700 mb-4">درصد افت ۶ ماه اخیر</h2>
      <LineChart :chart-data="performanceChartData" :chart-options="chartOptions" />
    </div>

  </div>
  </AppLayout>

</template>


<script>
import { onMounted, ref, computed } from 'vue';
import { collection, query, where, getDocs, Timestamp } from 'firebase/firestore';
import { db } from '@/firebase';
import AppLayout from '@/components/AppLayout.vue';
import { Bar, Line, } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement
} from 'chart.js';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import { vazirmatnFontBase64 } from '@/utils/vazirmatn.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, LineElement, CategoryScale, LinearScale, PointElement);

const BarChart = {
  name: 'BarChart',
  props: ['chartData', 'chartOptions'],
  components: { Bar },
  template: `<Bar :data="chartData" :options="chartOptions" />`
}

const LineChart = {
  name: 'LineChart',
  props: ['chartData', 'chartOptions'],
  components: { Line },
  template: `<Line :data="chartData" :options="chartOptions" />`
}

export default {
  components: { AppLayout, BarChart, LineChart },
  setup() {
    const totalPieces = ref(0);
    const totalWarnings = ref(0);
    const performanceDrop = ref(0);
    const healthIndex = ref(100);
    const chartData = ref({});
    const performanceChartData = ref({});
    const chartOptions = ref({ responsive: true });
    const role = ref('');

    const loadStats = async () => {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const startTimestamp = Timestamp.fromDate(firstDay);

      const batchQuery = query(collection(db, 'batches'), where('timestamp', '>=', startTimestamp));
      const batchSnapshot = await getDocs(batchQuery);

      let total = 0;
      let totalExpected = 0;
      let totalDiff = 0;

      batchSnapshot.forEach(doc => {
        const data = doc.data();
        total += data.count;
        totalExpected += data.expectedCount;
        totalDiff += (data.expectedCount - data.count);
      });

      totalPieces.value = total;

      let drop = 0;
      if (totalExpected > 0) {
        drop = (totalDiff / totalExpected) * 100;
      }
      performanceDrop.value = drop;

      const warningsQuery = query(collection(db, 'warnings'), where('timestamp', '>=', startTimestamp));
      const warningsSnapshot = await getDocs(warningsQuery);
      totalWarnings.value = warningsSnapshot.size;

      const warningPenalty = totalWarnings.value * 1.5;
      let score = 100 - (drop + warningPenalty);
      if (score < 0) score = 0;
      if (score > 100) score = 100;
      healthIndex.value = score;
    };

    const loadChartData = async () => {
      const months = [];
      const counts = [];
      const now = new Date();

      for (let i = 5; i >= 0; i--) {
        const target = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const label = target.toLocaleString('fa-IR', { month: 'long', year: 'numeric' });
        months.push(label);

        const start = Timestamp.fromDate(new Date(target.getFullYear(), target.getMonth(), 1));
        const end = Timestamp.fromDate(new Date(target.getFullYear(), target.getMonth() + 1, 1));

        const q = query(collection(db, 'batches'), where('timestamp', '>=', start), where('timestamp', '<', end));
        const snap = await getDocs(q);

        let sum = 0;
        snap.forEach(doc => sum += doc.data().count);
        counts.push(sum);
      }

      chartData.value = {
        labels: months,
        datasets: [
          {
            label: 'تعداد تولید',
            data: counts,
            backgroundColor: '#4CAF50'
          }
        ]
      };
    };

    const loadPerformanceChartData = async () => {
      const months = [];
      const drops = [];
      const now = new Date();

      for (let i = 5; i >= 0; i--) {
        const target = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const label = target.toLocaleString('fa-IR', { month: 'long', year: 'numeric' });
        months.push(label);

        const start = Timestamp.fromDate(new Date(target.getFullYear(), target.getMonth(), 1));
        const end = Timestamp.fromDate(new Date(target.getFullYear(), target.getMonth() + 1, 1));

        const q = query(collection(db, 'batches'), where('timestamp', '>=', start), where('timestamp', '<', end));
        const snap = await getDocs(q);

        let sumExpected = 0;
        let sumDiff = 0;

        snap.forEach(doc => {
          const data = doc.data();
          sumExpected += data.expectedCount;
          sumDiff += (data.expectedCount - data.count);
        });

        let drop = 0;
        if (sumExpected > 0) {
          drop = (sumDiff / sumExpected) * 100;
        }
        drops.push(drop.toFixed(1));
      }

      performanceChartData.value = {
        labels: months,
        datasets: [
          {
            label: 'درصد افت',
            data: drops,
            borderColor: '#FF5722',
            backgroundColor: 'rgba(255, 87, 34, 0.2)',
            tension: 0.3
          }
        ]
      };
    };

    const healthColorClass = computed(() => {
      if (healthIndex.value >= 80) return 'bg-green-200';
      if (healthIndex.value >= 50) return 'bg-yellow-200';
      return 'bg-red-200';
    });

    const healthEmoji = computed(() => {
      if (healthIndex.value >= 80) return '😄';
      if (healthIndex.value >= 50) return '😐';
      return '😟';
    });

    const generatePDF = async () => {
      const element = document.querySelector('#dashboard-content');
      const canvas = await html2canvas(element, { scale: 2 });
      const imgData = canvas.toDataURL('image/png');

      const pdf = new jsPDF('p', 'mm', 'a4');
      pdf.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64);
      pdf.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal');
      pdf.setFont('Vazirmatn');

      const pageWidth = pdf.internal.pageSize.getWidth();
      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pageWidth - 20;
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      pdf.addImage(imgData, 'PNG', 10, 20, pdfWidth, pdfHeight);
      pdf.save('factory-dashboard-report.pdf');
    };

    onMounted(() => {
      loadStats();
      loadChartData();
      loadPerformanceChartData();
      role.value = localStorage.getItem('userRole');
    });

    return {
      totalPieces,
      totalWarnings,
      performanceDrop,
      healthIndex,
      chartData,
      performanceChartData,
      chartOptions,
      healthColorClass,
      healthEmoji,
      role,
      generatePDF
    }
  }
}
</script>

