<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';
import { bloom, grain } from '@/lib/brand';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const brand = usePage().props.brand;

let wasDark = false;
onMounted(() => {
    const root = document.documentElement;
    wasDark = root.classList.contains('dark');
    root.classList.remove('dark');
    root.style.colorScheme = 'light';
});
onBeforeUnmount(() => {
    const root = document.documentElement;
    root.style.colorScheme = '';
    if (wasDark) {
        root.classList.add('dark');
    }
});
</script>

<template>
    <div
        class="auth-page marketing-page ds-root relative isolate flex min-h-svh flex-col overflow-hidden bg-white font-['Inter',ui-sans-serif,system-ui,sans-serif] text-(--ds-text) antialiased"
    >
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 -z-10 flex justify-center px-2 sm:px-4"
        >
            <div class="flex w-full max-w-[1408px] border-x border-[#E6E6E6]">
                <div
                    class="mx-auto flex w-full max-w-[1348px] border-x border-[#E6E6E6]"
                >
                    <div
                        class="hidden w-[274px] shrink-0 gap-[70px] pl-[68px] xl:flex"
                    >
                        <div
                            v-for="rail in ['a', 'b']"
                            :key="rail"
                            :class="[
                                'h-full w-[68px] border-x border-[#E6E6E6] bg-cover bg-top',
                                `auth-rail-${rail}`,
                            ]"
                        />
                    </div>
                    <div
                        class="auth-dots min-w-0 flex-1 border-[#E6E6E6] bg-cover bg-center opacity-80 xl:border-x"
                    />
                    <div
                        class="hidden w-[274px] shrink-0 gap-[70px] pl-[68px] xl:flex"
                    >
                        <div
                            v-for="rail in ['a', 'b']"
                            :key="rail"
                            :class="[
                                'h-full w-[68px] border-x border-[#E6E6E6] bg-cover bg-top',
                                `auth-rail-${rail}`,
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[78svh] min-h-[560px] mask-[linear-gradient(black_0%,black_55%,rgb(0_0_0/0.55)_78%,transparent)]"
            :style="{ backgroundImage: bloom }"
        >
            <div
                class="absolute inset-0 opacity-[0.05]"
                :style="{ backgroundImage: grain }"
            />
        </div>

        <main class="flex grow items-center justify-center px-4 py-12">
            <div class="cs-rise w-full max-w-[420px]">
                <Link
                    :href="home()"
                    :aria-label="$t('Back to ServerOS')"
                    class="group mx-auto mb-7 flex w-fit items-center gap-2.5 text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                >
                    <img
                        src="/images/landing/serveros-logo.svg"
                        alt=""
                        class="size-[22px] object-contain transition-transform duration-500 group-hover:rotate-60"
                    />
                    <span class="text-[19px]/6 font-medium tracking-[-0.03em]"
                        >ServerOS</span
                    >
                </Link>
                <div
                    class="ds-modal workspace-panel auth-form auth-compact overflow-hidden border border-[#E6E6E6] bg-white px-8 pt-8 pb-7 shadow-[0_24px_48px_-20px_rgb(2_16_56/0.35)]!"
                >
                    <div
                        v-if="title || description"
                        class="mb-7 flex flex-col items-center text-center"
                    >
                        <h1
                            v-if="title"
                            class="font-['JetBrains_Mono',ui-monospace,monospace] text-[22px]/7 font-medium tracking-[-0.03em] text-black"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="mt-2 text-[14px]/5 text-pretty text-[#666666]"
                        >
                            {{ description }}
                        </p>
                    </div>
                    <slot />
                </div>
                <p
                    v-if="brand.terms && brand.privacy"
                    class="mx-auto mt-5 w-fit bg-white px-2 text-center text-[12.5px]/5 text-balance text-[#666666]"
                >
                    {{ ' ' + $t('By continuing you agree to our') }}
                    <a
                        :href="brand.terms"
                        class="text-[#14130E] underline decoration-[#CCCCCC] underline-offset-2 hover:decoration-[#14130E]"
                        >{{ $t('Terms') }}</a
                    >
                    {{ $t('and') }}
                    <a
                        :href="brand.privacy"
                        class="text-[#14130E] underline decoration-[#CCCCCC] underline-offset-2 hover:decoration-[#14130E]"
                        >{{ $t('Privacy policy') }}</a
                    >.
                </p>
                <p
                    v-else-if="brand.terms || brand.privacy"
                    class="mx-auto mt-5 w-fit bg-white px-2 text-center text-[12.5px]/5 text-balance text-[#666666]"
                >
                    {{ ' ' + $t('By continuing you agree to our') }}
                    <a
                        :href="brand.terms ?? brand.privacy ?? undefined"
                        class="text-[#14130E] underline decoration-[#CCCCCC] underline-offset-2 hover:decoration-[#14130E]"
                        >{{
                            brand.terms ? $t('Terms') : $t('Privacy policy')
                        }}</a
                    >.
                </p>
            </div>
        </main>

        <footer
            v-if="brand.company"
            class="py-6 text-center text-[12px]/4 text-[#999999]"
        >
            <span class="bg-white px-2">{{
                `© ${new Date().getFullYear()} ${brand.company}.`
            }}</span>
        </footer>
    </div>
</template>
