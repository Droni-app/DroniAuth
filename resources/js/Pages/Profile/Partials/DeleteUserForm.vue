<script setup>
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.$el?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.$el?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-slate-400">
            Una vez eliminada tu cuenta, todos tus datos serán borrados permanentemente.
            Descarga cualquier información que desees conservar antes de continuar.
        </p>

        <DuiButton color="danger" @click="confirmUserDeletion">
            Eliminar mi cuenta
        </DuiButton>

        <DuiModal
            v-model="confirmingUserDeletion"
            title="¿Estás seguro de que quieres eliminar tu cuenta?"
            color="danger"
            @close="closeModal"
        >
            <div class="space-y-4">
                <p class="text-sm">
                    Esta acción es irreversible. Ingresa tu contraseña para confirmar.
                </p>

                <DuiLabel title="Contraseña" :error="form.errors.password">
                    <DuiInput
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        @keyup.enter="deleteUser"
                    />
                </DuiLabel>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <DuiButton variant="outline" color="neutral" @click="closeModal">
                        Cancelar
                    </DuiButton>
                    <DuiButton
                        color="danger"
                        :loading="form.processing"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Eliminar cuenta
                    </DuiButton>
                </div>
            </template>
        </DuiModal>
    </div>
</template>
