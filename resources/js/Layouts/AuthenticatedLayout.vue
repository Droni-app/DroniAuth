<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isDark, toggle } = useTheme();

const navItems = computed(() => [
    {
        label: 'Dashboard',
        to: route('dashboard'),
        active: route().current('dashboard'),
        icon: 'mdi mdi-view-dashboard-outline',
    },
    {
        label: 'Aplicaciones',
        to: route('clients.index'),
        active: route().current('clients.*'),
        icon: 'mdi mdi-apps',
    },
]);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        <DuiNavbar :items="navItems">
            <template #brand>
                <Link :href="route('dashboard')">
                    <img :src="isDark ? '/img/logo-w.svg' : '/img/logo.svg'" alt="DroniAuth" class="h-7" />
                </Link>
            </template>
            <template #actions>
                <div class="flex items-center gap-3">
                    <DuiTooltip :text="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'" placement="bottom" size="sm">
                        <template #trigger>
                            <button
                                @click="toggle"
                                class="flex items-center justify-center w-8 h-8 rounded-full text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                            >
                                <i :class="isDark ? 'mdi mdi-weather-sunny' : 'mdi mdi-weather-night'" class="text-lg"></i>
                            </button>
                        </template>
                    </DuiTooltip>
                    <Link :href="route('profile.edit')">
                        <DuiButton variant="ghost" size="sm" color="neutral">
                            <i class="mdi mdi-account-circle-outline mr-1"></i>
                            {{ user.name }}
                        </DuiButton>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button">
                        <DuiButton variant="outline" size="sm" color="danger">
                            <i class="mdi mdi-logout mr-1"></i>
                            Salir
                        </DuiButton>
                    </Link>
                </div>
            </template>
        </DuiNavbar>

        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <slot />
        </main>

        <footer class="mt-auto py-6 text-center text-xs text-slate-400">
            &copy; 2025 Construido con trabajo duro y mucho café por el equipo de
            <a href="https://droni.co" target="_blank" class="hover:text-white transition-colors">Droni.co</a>.
            <a href="/legal" class="ml-1 hover:text-white transition-colors">Legal</a>
        </footer>
    </div>
</template>
