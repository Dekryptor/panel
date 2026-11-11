<script setup lang="ts">
import { computed } from 'vue';

type Props = {
    values: (number | null)[];
    max?: number;
    rows?: number;
    columns?: number;
    color: string;
};

const {
    values,
    max = 100,
    rows = 8,
    columns = 30,
    color,
} = defineProps<Props>();

const PITCH = 9;
const RADIUS = 2.6;

const series = computed(() => {
    const size = Math.ceil(values.length / columns);

    return Array.from({ length: Math.min(columns, values.length) }, (_, i) => {
        const slice = values
            .slice(i * size, i * size + size)
            .filter((v): v is number => v !== null);

        return slice.length > 0
            ? slice.reduce((sum, v) => sum + v, 0) / slice.length
            : null;
    });
});

const dots = computed(() =>
    series.value.flatMap((value, column) => {
        const lit =
            value === null
                ? 0
                : Math.max(1, Math.round((Math.min(value, max) / max) * rows));

        return Array.from({ length: rows }, (_, row) => ({
            key: `${column}-${row}`,
            cx: column * PITCH + PITCH / 2,
            cy: (rows - row - 1) * PITCH + PITCH / 2,
            lit: row < lit,
            last: column === series.value.length - 1,
        }));
    }),
);
</script>

<template>
    <svg
        :viewBox="`0 0 ${series.length * PITCH} ${rows * PITCH}`"
        class="block h-auto w-full"
        aria-hidden="true"
    >
        <circle
            v-for="dot in dots"
            :key="dot.key"
            :cx="dot.cx"
            :cy="dot.cy"
            :r="RADIUS"
            :fill="dot.lit ? color : 'var(--ds-border-strong)'"
            :fill-opacity="dot.lit ? (dot.last ? 1 : 0.8) : 0.55"
        />
    </svg>
</template>
