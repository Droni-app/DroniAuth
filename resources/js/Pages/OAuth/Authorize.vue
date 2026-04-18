<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    client: Object,
    user: Object,
    scopes: Array,
    request: Object,
    authToken: String,
});

const csrf = usePage().props.csrf_token;
</script>

<template>
    <GuestLayout>
        <Head title="Autorizar aplicación" />

        <div class="space-y-5">
            <!-- Encabezado -->
            <div class="text-center">
                <p class="text-sm text-slate-400">
                    <strong class="text-white">{{ client.name }}</strong>
                    solicita acceso a tu cuenta
                </p>
            </div>

            <!-- Scopes -->
            <div v-if="scopes.length" class="space-y-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Esta aplicación podrá:</p>
                <ul class="space-y-1.5">
                    <li
                        v-for="scope in scopes"
                        :key="scope.id"
                        class="flex items-start gap-2 text-sm text-slate-300"
                    >
                        <i class="mdi mdi-check-circle-outline text-green-400 mt-0.5 shrink-0" />
                        {{ scope.description }}
                    </li>
                </ul>
            </div>

            <!-- Acciones: formularios HTML nativos, sin JS de por medio -->
            <div class="flex gap-3 pt-2">
                <form method="POST" action="/oauth/authorize" class="flex-1">
                    <input type="hidden" name="_token" :value="csrf" />
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="auth_token" :value="authToken" />
                    <DuiButton type="submit" color="neutral" variant="outline" class="w-full">
                        Denegar
                    </DuiButton>
                </form>

                <form method="POST" action="/oauth/authorize" class="flex-1">
                    <input type="hidden" name="_token" :value="csrf" />
                    <input type="hidden" name="auth_token" :value="authToken" />
                    <DuiButton type="submit" color="primary" class="w-full">
                        Autorizar
                    </DuiButton>
                </form>
            </div>

            <p class="text-center text-xs text-slate-500">
                Autenticado como <strong class="text-slate-400">{{ user.email }}</strong>
            </p>
        </div>
    </GuestLayout>
</template>
