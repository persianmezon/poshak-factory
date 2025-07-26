<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
    <DashboardCard icon="📦" title="کل ثبت‌های امروز" :value="totalCountToday" :change="totalChange" />
    <DashboardCard icon="✂️" title="برش امروز" :value="sectionStats['برش'].today" :change="sectionStats['برش'].change" />
    <DashboardCard icon="🧵" title="دوخت امروز" :value="sectionStats['دوخت'].today" :change="sectionStats['دوخت'].change" />
    <DashboardCard icon="✅" title="نهایی‌کار امروز" :value="sectionStats['نهایی‌کار'].today" :change="sectionStats['نهایی‌کار'].change" />
    <DashboardCard icon="🏆" title="کارگر فعال" :value="topWorkerText" />
    <DashboardCard icon="🕒" title="آخرین ثبت" :value="latestText" />
  </div>
</template>

<script>
import DashboardCard from '@/components/DashboardCard.vue'

export default {
  components: { DashboardCard },
  data() {
    return {
      scans: [],
      workers: []
    }
  },
  async mounted() {
    const [scansRes, workersRes] = await Promise.all([
      fetch('https://app.paryamezon.ir/scans.json'),
      fetch('https://app.paryamezon.ir/api/get-workers.php')
    ])
    const scanJson = await scansRes.json()
    const workerJson = await workersRes.json()
    this.scans = scanJson.records || []
    this.workers = workerJson.workers || []
  },
computed: {
  todayDate() {
    return new Date().toISOString().substr(0, 10)
  },
  yesterdayDate() {
    const d = new Date()
    d.setDate(d.getDate() - 1)
    return d.toISOString().substr(0, 10)
  },
  todayScans() {
    const today = this.todayDate
    return this.scans.filter(r =>
      new Date(r.createdAt * 1000).toISOString().substr(0, 10) === today
    )
  },
  yesterdayScans() {
    const yesterday = this.yesterdayDate
    return this.scans.filter(r =>
      new Date(r.createdAt * 1000).toISOString().substr(0, 10) === yesterday
    )
  },
  totalCountToday() {
    return this.todayScans.reduce((sum, r) => sum + (r.count || 0), 0)
  },
  totalCountYesterday() {
    return this.yesterdayScans.reduce((sum, r) => sum + (r.count || 0), 0)
  },
  totalChange() {
    const y = this.totalCountYesterday
    if (y === 0) return null
    return Math.round(((this.totalCountToday - y) / y) * 100)
  },
  sectionStats() {
    const sections = ['برش', 'دوخت', 'نهایی‌کار']
    const stats = {}
    const today = this.todayDate
    const yesterday = this.yesterdayDate
    for (const section of sections) {
      const todaySum = this.scans
        .filter(r => r.section === section && new Date(r.createdAt * 1000).toISOString().substr(0, 10) === today)
        .reduce((s, r) => s + (r.count || 0), 0)

      const yesterdaySum = this.scans
        .filter(r => r.section === section && new Date(r.createdAt * 1000).toISOString().substr(0, 10) === yesterday)
        .reduce((s, r) => s + (r.count || 0), 0)

      const change = yesterdaySum === 0 ? null : Math.round(((todaySum - yesterdaySum) / yesterdaySum) * 100)
      stats[section] = { today: todaySum, yesterday: yesterdaySum, change }
    }
    return stats
  },
  topWorkerText() {
    const map = {}
    this.todayScans.forEach(r => {
      map[r.workerId] = (map[r.workerId] || 0) + (r.count || 0)
    })
    const topEntry = Object.entries(map).sort((a, b) => b[1] - a[1])[0]
    if (!topEntry) return '---'
    const [uid, count] = topEntry
    const name = this.workers.find(w => w.uid === uid)?.name || '---'
    return `${name} (${count})`
  },
  latestText() {
    if (!this.todayScans.length) return '---'
    const latest = [...this.todayScans].sort((a, b) => b.createdAt - a.createdAt)[0]
    const time = new Date(latest.createdAt * 1000).toLocaleTimeString('fa-IR')
    return `${latest.section} | ${latest.code} | ${time}`
  }
}
}
</script>
