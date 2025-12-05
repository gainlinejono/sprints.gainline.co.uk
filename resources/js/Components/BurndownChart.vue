<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import type { BurndownData } from '@/Types';

interface Props {
    data: BurndownData[];
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Sprint Burndown',
});

const chartOptions = computed(() => ({
    chart: {
        type: 'line',
        height: 350,
        toolbar: {
            show: false,
        },
        zoom: {
            enabled: false,
        },
        fontFamily: 'Inter, sans-serif',
    },
    colors: ['#6366f1', '#94a3b8'],
    stroke: {
        width: [3, 2],
        curve: 'smooth',
        dashArray: [0, 5],
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'light',
            type: 'vertical',
            shadeIntensity: 0.3,
            gradientToColors: ['#6366f1', '#94a3b8'],
            inverseColors: false,
            opacityFrom: 0.5,
            opacityTo: 0,
            stops: [0, 100],
        },
    },
    markers: {
        size: 4,
        colors: ['#6366f1', '#94a3b8'],
        strokeColors: '#fff',
        strokeWidth: 2,
        hover: {
            size: 6,
        },
    },
    grid: {
        borderColor: '#e5e7eb',
        strokeDashArray: 4,
        xaxis: {
            lines: {
                show: true,
            },
        },
        yaxis: {
            lines: {
                show: true,
            },
        },
    },
    xaxis: {
        categories: props.data.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
        }),
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
            },
        },
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
    },
    yaxis: {
        min: 0,
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
            },
            formatter: (val: number) => `${val}h`,
        },
    },
    legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'right',
        labels: {
            colors: '#6b7280',
        },
    },
    tooltip: {
        theme: 'light',
        y: {
            formatter: (val: number) => `${val} hours`,
        },
    },
}));

const series = computed(() => [
    {
        name: 'Actual Remaining',
        data: props.data.map(d => d.actual_remaining),
    },
    {
        name: 'Ideal Burndown',
        data: props.data.map(d => d.ideal_remaining),
    },
]);
</script>

<template>
    <div class="chart-container">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ title }}</h3>
        <VueApexCharts
            type="area"
            height="350"
            :options="chartOptions"
            :series="series"
        />
    </div>
</template>
