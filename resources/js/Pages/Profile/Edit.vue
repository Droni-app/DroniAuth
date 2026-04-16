<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    twoFactorEnabled: Boolean,
    requiresTwoFactorConfirmation: Boolean,
});

const user = computed(() => usePage().props.auth.user);

const initials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name
        .split(' ')
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase();
});

const memberSince = computed(() => {
    if (!user.value?.created_at) return null;
    return new Date(user.value.created_at).toLocaleDateString('es-MX', {
        month: 'long',
        year: 'numeric',
    });
});
</script>

<template>
    <Head title="Mi perfil" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Cabecera de usuario -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <!-- Avatar -->
                    <div class="shrink-0">
                        <img
                            v-if="user.avatar"
                            :src="user.avatar"
                            :alt="user.name"
                            class="w-16 h-16 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700"
                        />
                        <div
                            v-else
                            class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center ring-2 ring-slate-200 dark:ring-slate-700"
                        >
                            <span class="text-xl font-bold text-white">{{ initials }}</span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-semibold text-slate-900 dark:text-white truncate">
                            {{ user.name }}
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ user.email }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span
                                v-if="user.email_verified_at"
                                class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400"
                            >
                                <i class="mdi mdi-check-circle text-xs"></i>
                                Correo verificado
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400"
                            >
                                <i class="mdi mdi-alert-circle text-xs"></i>
                                Correo sin verificar
                            </span>
                            <span
                                v-if="user.two_factor_enabled"
                                class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400"
                            >
                                <i class="mdi mdi-shield-check text-xs"></i>
                                2FA activo
                            </span>
                            <span
                                v-if="memberSince"
                                class="text-xs text-slate-400 dark:text-slate-500"
                            >
                                Miembro desde {{ memberSince }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout de dos columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna principal (izquierda) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Información del perfil -->
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                                <i class="mdi mdi-account-outline text-primary-600 dark:text-primary-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Información del perfil</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Actualiza tu nombre y correo electrónico</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center">
                                <i class="mdi mdi-lock-outline text-violet-600 dark:text-violet-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Contraseña</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Usa una contraseña larga y aleatoria para mayor seguridad</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <UpdatePasswordForm />
                        </div>
                    </div>

                </div>

                <!-- Columna secundaria (derecha) -->
                <div class="space-y-6">

                    <!-- Autenticación en dos pasos -->
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                <i class="mdi mdi-shield-lock-outline text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Verificación en dos pasos</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Protección adicional con TOTP</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <TwoFactorAuthenticationForm :requires-confirmation="requiresTwoFactorConfirmation" />
                        </div>
                    </div>

                    <!-- Zona de peligro -->
                    <div class="rounded-xl border border-red-200 dark:border-red-900/60 bg-white dark:bg-slate-800/60 overflow-hidden">
                        <div class="px-6 py-4 border-b border-red-100 dark:border-red-900/40 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                                <i class="mdi mdi-alert-outline text-red-600 dark:text-red-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-red-700 dark:text-red-400">Zona de peligro</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Acciones irreversibles</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <DeleteUserForm />
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
