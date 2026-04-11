<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-5">
        <DuiLabel title="Nombre completo" required :error="form.errors.name">
            <DuiInput
                v-model="form.name"
                type="text"
                autocomplete="name"
                autofocus
                required
            />
        </DuiLabel>

        <DuiLabel title="Correo electrónico" required :error="form.errors.email">
            <DuiInput
                v-model="form.email"
                type="email"
                autocomplete="username"
                required
            />
        </DuiLabel>

        <div v-if="mustVerifyEmail && user.email_verified_at === null">
            <DuiAlert color="warning" variant="ghost" class="mt-2">
                Tu correo no está verificado.
                <Link :href="route('verification.send')" method="post" as="button" class="underline ml-1">
                    Reenviar verificación
                </Link>
            </DuiAlert>
            <DuiAlert v-if="status === 'verification-link-sent'" color="success" class="mt-2">
                Se envió un nuevo enlace de verificación a tu correo.
            </DuiAlert>
        </div>

        <div class="flex items-center gap-4">
            <DuiButton type="submit" color="primary" :loading="form.processing" :disabled="form.processing">
                Guardar cambios
            </DuiButton>
            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <DuiAlert v-if="form.recentlySuccessful" color="success" variant="ghost">
                    Guardado.
                </DuiAlert>
            </Transition>
        </div>
    </form>
</template>
