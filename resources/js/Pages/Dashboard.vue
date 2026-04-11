<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <DuiCard :title="`Bienvenido, ${user.name}`" subtitle="DroniAuth — Panel de control">
                <p class="text-slate-400 text-sm mt-2">
                    Has iniciado sesión correctamente. Desde aquí puedes gestionar tu cuenta
                    y las aplicaciones conectadas al ecosistema Droni.
                </p>
            </DuiCard>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <DuiCard title="Sesión activa" subtitle="Estado de autenticación">
                    <DuiAlert color="success" variant="ghost" class="mt-2">
                        Conectado como {{ user.email }}
                    </DuiAlert>
                </DuiCard>

                <DuiCard title="Seguridad" subtitle="Configuración de cuenta">
                    <p class="text-slate-400 text-sm mt-2">Gestiona tu contraseña y preferencias de seguridad.</p>
                </DuiCard>

                <DuiCard title="Aplicaciones" subtitle="Clientes OAuth2">
                    <p class="text-slate-400 text-sm mt-2">Revisa las aplicaciones con acceso a tu cuenta.</p>
                </DuiCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
