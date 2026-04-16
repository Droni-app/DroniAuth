<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Nueva contraseña" />

        <form @submit.prevent="submit" class="space-y-5">
            <DuiLabel title="Correo electrónico" required :error="form.errors.email">
                <DuiInput
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    autofocus
                    required
                />
            </DuiLabel>

            <DuiLabel title="Nueva contraseña" required :error="form.errors.password">
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
                    placeholder="Repite tu nueva contraseña"
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
                Restablecer contraseña
            </DuiButton>
        </form>
    </GuestLayout>
</template>
