<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.$el?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.$el?.focus();
            }
        },
    });
};
</script>

<template>
    <form @submit.prevent="updatePassword" class="space-y-5">
        <DuiLabel title="Contraseña actual" required :error="form.errors.current_password">
            <DuiInput
                ref="currentPasswordInput"
                v-model="form.current_password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="••••••••"
            />
        </DuiLabel>

        <DuiLabel title="Nueva contraseña" required :error="form.errors.password">
            <DuiInput
                ref="passwordInput"
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                required
                placeholder="Mínimo 8 caracteres"
            />
        </DuiLabel>

        <DuiLabel title="Confirmar nueva contraseña" required :error="form.errors.password_confirmation">
            <DuiInput
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                placeholder="Repite tu nueva contraseña"
            />
        </DuiLabel>

        <div class="flex items-center gap-4">
            <DuiButton type="submit" color="primary" :loading="form.processing" :disabled="form.processing">
                Actualizar contraseña
            </DuiButton>
            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <DuiAlert v-if="form.recentlySuccessful" color="success" variant="ghost">
                    Guardado.
                </DuiAlert>
            </Transition>
        </div>
    </form>
</template>
