<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';

const { isDark } = useTheme();
const oauthClient = computed(() => usePage().props.oauth_client);
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 p-4">
        <div class="mb-8 text-center">
            <!-- Branding del cliente OAuth si aplica -->
            <template v-if="oauthClient">
                <img v-if="oauthClient.logo" :src="oauthClient.logo" :alt="oauthClient.name" class="h-10 mx-auto object-contain mb-2" />
                <p v-else class="text-xl font-semibold text-slate-900 dark:text-white mb-2">{{ oauthClient.name }}</p>
                <div class="flex items-center justify-center gap-2">
                    <div class="h-px w-8 bg-slate-400/40"></div>
                    <p class="text-slate-400 text-xs">{{ oauthClient.name }}</p>
                    <div class="h-px w-8 bg-slate-400/40"></div>
                </div>
            </template>
            <!-- Branding de DroniAuth por defecto -->
            <template v-else>
                <img :src="isDark ? '/img/brand-w.svg' : '/img/brand.svg'" alt="DroniAuth" class="h-10 mx-auto mb-2" />
                <p class="text-slate-400 text-sm">Identity & Access Management</p>
            </template>
        </div>
        <div class="w-full max-w-md">
            <DuiCard>
                <slot />
            </DuiCard>
        </div>

        <footer class="mt-8 text-center text-xs text-slate-500">
            &copy; 2025 Construido con trabajo duro y mucho café por el equipo de
            <a href="https://droni.co" target="_blank" class="hover:text-white transition-colors">Droni.co</a>.
            <a href="/legal" class="ml-1 hover:text-white transition-colors">Legal</a>
        </footer>
    </div>
</template>
