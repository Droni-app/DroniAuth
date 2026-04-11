<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ status: String });

const form = useForm({});
const submit = () => form.post(route('verification.send'));
const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verificar email" />

        <p class="text-sm text-slate-400 mb-5">
            Gracias por registrarte. Verifica tu correo haciendo clic en el enlace que te enviamos.
            Si no lo recibiste, podemos enviarte otro.
        </p>

        <DuiAlert v-if="verificationLinkSent" color="success" class="mb-5">
            Se envió un nuevo enlace de verificación a tu correo.
        </DuiAlert>

        <form @submit.prevent="submit" class="space-y-4">
            <DuiButton
                type="submit"
                color="primary"
                block
                :loading="form.processing"
                :disabled="form.processing"
            >
                Reenviar email de verificación
            </DuiButton>

            <Link :href="route('logout')" method="post" as="button" class="w-full">
                <DuiButton variant="ghost" color="neutral" block>
                    Cerrar sesión
                </DuiButton>
            </Link>
        </form>
    </GuestLayout>
</template>
