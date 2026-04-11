<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

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

            <DuiLabel title="Contraseña" required :error="form.errors.password">
                <DuiInput
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                />
            </DuiLabel>

            <div class="flex items-center justify-between">
                <DuiCheckbox v-model="form.remember" label="Recordarme" />

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-slate-400 hover:text-white transition-colors"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <DuiButton
                type="submit"
                color="primary"
                block
                :loading="form.processing"
                :disabled="form.processing"
            >
                Iniciar sesión
            </DuiButton>

            <p class="text-center text-sm text-slate-400">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="text-white hover:underline">
                    Regístrate
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
