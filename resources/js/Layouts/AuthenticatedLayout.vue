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
    },
    {
        label: 'Clientes OAuth',
        to: route('clients.index'),
        active: route().current('clients.*'),
    },
]);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        <DuiNavbar :items="navItems">
            <template #brand>
                <Link :href="route('dashboard')" class="text-white font-bold text-lg tracking-tight">
                    DroniAuth
                </Link>
            </template>
            <template #actions>
                <div class="flex items-center gap-3">
                    <button
                        @click="toggle"
                        class="flex items-center justify-center w-8 h-8 rounded-full text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                        :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                    >
                        <i :class="isDark ? 'mdi mdi-weather-sunny' : 'mdi mdi-weather-night'" class="text-lg"></i>
                    </button>
                    <Link :href="route('profile.edit')">
                        <DuiButton variant="ghost" size="sm" color="neutral">
                            {{ user.name }}
                        </DuiButton>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button">
                        <DuiButton variant="outline" size="sm" color="danger">
                            Salir
                        </DuiButton>
                    </Link>
                </div>
            </template>
        </DuiNavbar>

        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <slot />
        </main>
    </div>
</template>
