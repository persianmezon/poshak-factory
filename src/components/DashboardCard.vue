<template>
  <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center text-center min-h-[120px]">
    <div class="text-3xl mb-1">{{ value }}</div>
    <div class="text-sm text-gray-500">{{ title }}</div>
    <div v-if="change !== null" :class="changeColorClass" class="text-xs mt-1 font-bold">
      {{ changePrefix }}{{ Math.abs(change) }}%
    </div>
  </div>
</template>

<script>
export default {
  name: "DashboardCard",
  props: {
    icon: String,
    title: String,
    value: [String, Number],
    change: {
      type: String,
      default: '0%'
    }
  },
  computed: {
    changePrefix() {
      return this.change > 0 ? '+' : this.change < 0 ? '-' : ''
    },
    changeColorClass() {
      if (this.change > 0) return 'text-green-500'
      if (this.change < 0) return 'text-red-500'
      return 'text-gray-400'
    }
  }
}

</script>
