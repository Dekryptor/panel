<script setup lang="ts">
import { computed } from 'vue';

type Props = {
    groups: { count: number; color: string }[];
    total: number;
    columns?: number;
    rows?: number;
};

const { groups, total, columns = 30, rows = 7 } = defineProps<Props>();

const PITCH = 9;
const RADIUS = 2.6;

const dots = computed(() => {
    const size = columns * rows;
    const scale = total > size ? size / total : 1;
    const colors = groups.flatMap((group) =>
        Array.from(
            { length: Math.round(group.count * scale) },
            () => group.color,
        ),
    );
    const shown = Math.min(
        size,
        Math.max(Math.round(total * scale), colors.length),
    );

    return Array.from({ length: size }, (_, index) => ({
        key: index,
        cx: (index % columns) * PITCH + PITCH / 2,
        cy: Math.floor(index / columns) * PITCH + PITCH / 2,
        fill: colors[index] ?? 'var(--ds-border-strong)',
        opacity: colors[index] ? 0.9 : index < shown ? 0.9 : 0.4,
    }));
});
</script>

<template>
    <svg
        :viewBox="`0 0 ${columns * PITCH} ${rows * PITCH}`"
        class="block h-auto w-full"
        aria-hidden="true"
    >
        <circle
            v-for="dot in dots"
            :key="dot.key"
            :cx="dot.cx"
            :cy="dot.cy"
            :r="RADIUS"
            :fill="dot.fill"
            :fill-opacity="dot.opacity"
        />
    </svg>
</template>
