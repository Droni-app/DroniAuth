<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Mi perfil" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-2xl">
            <DuiCard title="Información del perfil" subtitle="Actualiza tu nombre y correo electrónico">
                <div class="mt-4">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                </div>
            </DuiCard>

            <DuiCard title="Contraseña" subtitle="Usa una contraseña larga y aleatoria para mayor seguridad">
                <div class="mt-4">
                    <UpdatePasswordForm />
                </div>
            </DuiCard>

            <DuiCard title="Eliminar cuenta" subtitle="Esta acción es irreversible">
                <div class="mt-4">
                    <DeleteUserForm />
                </div>
            </DuiCard>
        </div>
    </AuthenticatedLayout>
</template>
