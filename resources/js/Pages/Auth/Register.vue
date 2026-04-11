<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear cuenta" />

        <form @submit.prevent="submit" class="space-y-5">
            <DuiLabel title="Nombre completo" required :error="form.errors.name">
                <DuiInput
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    autofocus
                    required
                    placeholder="Juan Pérez"
                />
            </DuiLabel>

            <DuiLabel title="Correo electrónico" required :error="form.errors.email">
                <DuiInput
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    required
                    placeholder="tu@email.com"
                />
            </DuiLabel>

            <DuiLabel title="Contraseña" required :error="form.errors.password">
                <DuiInput
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    required
                    placeholder="Mínimo 8 caracteres"
                />
            </DuiLabel>

            <DuiLabel title="Confirmar contraseña" required :error="form.errors.password_confirmation">
                <DuiInput
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    placeholder="Repite tu contraseña"
                />
            </DuiLabel>

            <DuiButton
                type="submit"
                color="primary"
                block
                :loading="form.processing"
                :disabled="form.processing"
            >
                Crear cuenta
            </DuiButton>

            <p class="text-center text-sm text-slate-400">
                ¿Ya tienes cuenta?
                <Link :href="route('login')" class="text-white hover:underline">
                    Inicia sesión
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
