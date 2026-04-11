<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

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
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <DuiNavbar :items="navItems">
            <template #brand>
                <Link :href="route('dashboard')" class="text-white font-bold text-lg tracking-tight">
                    DroniAuth
                </Link>
            </template>
            <template #actions>
                <div class="flex items-center gap-3">
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
