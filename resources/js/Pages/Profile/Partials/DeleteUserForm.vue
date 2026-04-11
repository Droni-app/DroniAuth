<script setup>
import Modal from '@/Components/Modal.vue';
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

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">
                    ¿Estás seguro de que quieres eliminar tu cuenta?
                </h2>
                <p class="text-sm text-gray-600">
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
            </div>
        </Modal>
    </div>
</template>
