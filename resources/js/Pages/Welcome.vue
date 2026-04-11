<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="DroniAuth" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex flex-col items-center justify-center p-6">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white tracking-tight mb-3">DroniAuth</h1>
            <p class="text-slate-400 text-lg max-w-md mx-auto">
                Sistema centralizado de identidad y acceso para el ecosistema Droni.
            </p>
        </div>

        <div class="flex gap-4 mb-16">
            <template v-if="canLogin">
                <Link :href="route('login')">
                    <DuiButton color="primary" size="lg">Iniciar sesión</DuiButton>
                </Link>
            </template>
            <template v-if="canRegister">
                <Link :href="route('register')">
                    <DuiButton variant="outline" color="neutral" size="lg">Crear cuenta</DuiButton>
                </Link>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl w-full">
            <DuiCard title="OAuth2 / OpenID" subtitle="Autorización estándar">
                <p class="text-slate-400 text-sm mt-2">
                    Implementación completa de OAuth2 con Laravel Passport para autenticación segura entre servicios.
                </p>
            </DuiCard>

            <DuiCard title="CIAM Centralizado" subtitle="Un solo punto de identidad">
                <p class="text-slate-400 text-sm mt-2">
                    Gestiona usuarios, roles y permisos de todos los proyectos Droni desde un único sistema.
                </p>
            </DuiCard>

            <DuiCard title="Ecosistema Droni" subtitle="Integración nativa">
                <p class="text-slate-400 text-sm mt-2">
                    Conecta DroniLand, FMN y cualquier proyecto futuro con autenticación centralizada.
                </p>
            </DuiCard>
        </div>
    </div>
</template>
