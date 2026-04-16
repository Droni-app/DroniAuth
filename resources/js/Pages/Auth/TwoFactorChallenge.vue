<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.reset('code', 'recovery_code');
};

const submit = () => {
    form.post(route('two-factor.login'), {
        onFinish: () => form.reset('code', 'recovery_code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verificación en dos pasos" />

        <div class="mb-5 text-sm text-slate-400">
            <template v-if="!recovery">
                Ingresa el código de 6 dígitos de tu aplicación de autenticación.
            </template>
            <template v-else>
                Ingresa uno de tus códigos de recuperación de emergencia.
            </template>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <DuiLabel
                v-if="!recovery"
                title="Código de autenticación"
                required
                :error="form.errors.code"
            >
                <DuiInput
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    placeholder="000000"
                />
            </DuiLabel>

            <DuiLabel
                v-else
                title="Código de recuperación"
                required
                :error="form.errors.recovery_code"
            >
                <DuiInput
                    v-model="form.recovery_code"
                    type="text"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    placeholder="xxxx-xxxx-xxxx"
                />
            </DuiLabel>

            <div class="flex items-center justify-between">
                <button
                    type="button"
                    class="text-sm text-slate-400 hover:text-white transition-colors"
                    @click="toggleRecovery"
                >
                    {{ recovery ? 'Usar código de autenticación' : 'Usar código de recuperación' }}
                </button>
            </div>

            <DuiButton
                type="submit"
                color="primary"
                block
                class="mt-2"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Verificar
            </DuiButton>
        </form>
    </GuestLayout>
</template>
