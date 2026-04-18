<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    client: Object,
    authorizedUsers: Array,
});

const grantLabel = (grants) => {
    if (grants?.includes('authorization_code')) return 'Authorization Code';
    if (grants?.includes('client_credentials')) return 'Client Credentials';
    return '—';
};

const columns = [
    { label: 'Usuario', name: 'user' },
    { label: 'Scopes', name: 'scopes' },
    { label: 'Autorizado', name: 'authorized_at' },
    { label: 'Expira', name: 'expires_at' },
];
</script>

<template>
    <Head :title="client.name" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('clients.index')" class="text-slate-400 hover:text-white transition-colors">
                    <i class="mdi mdi-arrow-left text-xl" />
                </Link>
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white truncate">{{ client.name }}</h1>
                            <img v-if="client.logo" :src="client.logo" :alt="client.name" class="h-7 max-w-[160px] object-contain" />
                        </div>
                        <p class="text-slate-400 text-sm mt-0.5">{{ grantLabel(client.grant_types) }}</p>
                    </div>
                </div>
                <Link :href="route('clients.index')">
                    <DuiButton variant="outline" color="neutral" size="sm">
                        <i class="mdi mdi-pencil-outline mr-1" />
                        Gestionar
                    </DuiButton>
                </Link>
            </div>

            <!-- Info del cliente -->
            <DuiCard title="Información del cliente">
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Client ID</p>
                        <p class="font-mono text-slate-300 break-all">{{ client.id }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Creado</p>
                        <p class="text-slate-300">{{ client.created_at.substring(0, 10) }}</p>
                    </div>
                    <div v-if="client.redirect_uris?.length">
                        <p class="text-slate-400 text-xs uppercase tracking-wide mb-1">Redirect URIs</p>
                        <div class="space-y-1">
                            <p v-for="uri in client.redirect_uris" :key="uri" class="font-mono text-slate-300 text-xs break-all">{{ uri }}</p>
                        </div>
                    </div>
                </div>
            </DuiCard>

            <!-- Usuarios autorizados -->
            <DuiCard :title="`Usuarios autorizados (${authorizedUsers.length})`">
                <template v-if="authorizedUsers.length > 0">
                    <DuiTable :columns="columns" :rows="authorizedUsers" class="mt-2">
                        <template #user="u">
                            <div class="flex items-center gap-3">
                                <img v-if="u.avatar" :src="u.avatar" :alt="u.name" class="w-8 h-8 rounded-full object-cover shrink-0" />
                                <div v-else class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center shrink-0">
                                    <i class="mdi mdi-account text-slate-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ u.name }}</p>
                                    <p class="text-xs text-slate-400">{{ u.email }}</p>
                                </div>
                            </div>
                        </template>

                        <template #scopes="u">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="scope in u.scopes"
                                    :key="scope"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs border border-slate-600 text-slate-400"
                                >
                                    {{ scope }}
                                </span>
                                <span v-if="!u.scopes?.length" class="text-slate-500 text-sm">—</span>
                            </div>
                        </template>

                        <template #authorized_at="u">
                            <span class="text-sm text-slate-400">{{ u.authorized_at?.substring(0, 10) ?? '—' }}</span>
                        </template>

                        <template #expires_at="u">
                            <span class="text-sm text-slate-400">{{ u.expires_at?.substring(0, 10) ?? '—' }}</span>
                        </template>
                    </DuiTable>
                </template>

                <div v-else class="text-center py-10 text-slate-400">
                    <i class="mdi mdi-account-off-outline text-4xl mb-2 block" />
                    <p class="text-sm">Ningún usuario ha autorizado esta aplicación todavía.</p>
                </div>
            </DuiCard>

        </div>
    </AuthenticatedLayout>
</template>
