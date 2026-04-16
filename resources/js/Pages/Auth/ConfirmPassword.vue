<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ password: '' });

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmar contraseña" />

        <DuiAlert color="warning" variant="ghost" class="mb-6">
            Esta es un área segura. Confirma tu contraseña para continuar.
        </DuiAlert>

        <form @submit.prevent="submit" class="space-y-5">
            <DuiLabel title="Contraseña" required :error="form.errors.password">
                <DuiInput
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    autofocus
                    required
                    placeholder="••••••••"
                />
            </DuiLabel>

            <DuiButton
                type="submit"
                color="primary"
                block
                class="mt-2"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Confirmar
            </DuiButton>
        </form>
    </GuestLayout>
</template>
