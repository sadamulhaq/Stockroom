<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps<{ data: { category: string; value: number }[] }>()

const chartData = computed(() => ({
  labels: props.data.map((d) => d.category),
  datasets: [
    {
      label: 'Stock Value ($)',
      data: props.data.map((d) => d.value),
      backgroundColor: '#10b981',
      borderRadius: 6,
    },
  ],
}))

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, ticks: { callback: (v: string | number) => `$${Number(v).toLocaleString()}` } } },
}
</script>

<template>
  <div class="h-64">
    <Bar :data="chartData" :options="options" />
  </div>
</template>
