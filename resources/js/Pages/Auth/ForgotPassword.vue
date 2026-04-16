<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '' });

const submit = () => form.post(route('password.email'));
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar contraseña" />

        <p class="text-sm text-slate-400 mb-6">
            Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        <DuiAlert v-if="status" color="success" class="mb-5">
            {{ status }}
        </DuiAlert>

        <form @submit.prevent="submit" class="space-y-5">
            <DuiLabel title="Correo electrónico" required :error="form.errors.email">
                <DuiInput
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    autofocus
                    required
                    placeholder="tu@email.com"
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
                Enviar enlace de recuperación
            </DuiButton>
        </form>
    </GuestLayout>
</template>
