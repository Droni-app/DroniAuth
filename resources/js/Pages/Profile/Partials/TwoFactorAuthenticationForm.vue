<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const secretKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({ code: '' });

const twoFactorEnabled = computed(() => !enabling.value && user.value?.two_factor_enabled);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactor = () => {
    enabling.value = true;

    axios.post(route('two-factor.enable'))
        .then(() => Promise.all([showQrCode(), showSecretKey(), showRecoveryCodes()]))
        .then(() => {
            confirming.value = props.requiresConfirmation;
        })
        .catch((error) => {
            if (error.response?.status === 423) {
                router.visit(route('password.confirm'));
            }
        })
        .finally(() => {
            enabling.value = false;
        });
};

const showQrCode = () => axios.get(route('two-factor.qr-code')).then(r => { qrCode.value = r.data.svg; });
const showSecretKey = () => axios.get(route('two-factor.secret-key')).then(r => { secretKey.value = r.data.secretKey; });
const showRecoveryCodes = () => axios.get(route('two-factor.recovery-codes')).then(r => { recoveryCodes.value = r.data; });

const confirmTwoFactor = () => {
    confirmationForm.processing = true;
    confirmationForm.clearErrors();

    axios.post(route('two-factor.confirm'), { code: confirmationForm.code })
        .then(() => {
            confirming.value = false;
            qrCode.value = null;
            secretKey.value = null;
            confirmationForm.reset();
            router.reload({ only: ['auth'] });
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                confirmationForm.setError('code', error.response.data.errors?.code?.[0] ?? 'Código inválido.');
            } else if (error.response?.status === 423) {
                router.visit(route('password.confirm'));
            }
        })
        .finally(() => {
            confirmationForm.processing = false;
        });
};

const regenerateRecoveryCodes = () => {
    axios.post(route('two-factor.recovery-codes')).then(() => showRecoveryCodes());
};

const disableTwoFactor = () => {
    disabling.value = true;

    axios.delete(route('two-factor.disable'))
        .then(() => {
            confirming.value = false;
            qrCode.value = null;
            secretKey.value = null;
            recoveryCodes.value = [];
            router.reload({ only: ['auth'] });
        })
        .catch((error) => {
            if (error.response?.status === 423) {
                router.visit(route('password.confirm'));
            }
        })
        .finally(() => {
            disabling.value = false;
        });
};
</script>

<template>
    <div class="space-y-4">

        <!-- Estado actual -->
        <div class="flex items-center gap-2">
            <div
                class="w-2 h-2 rounded-full shrink-0"
                :class="twoFactorEnabled && !confirming ? 'bg-emerald-400' : 'bg-slate-400 dark:bg-slate-600'"
            ></div>
            <span class="text-sm" :class="twoFactorEnabled && !confirming ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-500 dark:text-slate-400'">
                <template v-if="twoFactorEnabled && !confirming">Activa</template>
                <template v-else-if="confirming">Pendiente de confirmación</template>
                <template v-else>Inactiva</template>
            </span>
        </div>

        <!-- Descripción breve -->
        <p v-if="!twoFactorEnabled && !confirming" class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Al activarla, cada inicio de sesión requerirá un código temporal generado por una app como Google Authenticator o Authy.
        </p>

        <!-- QR code + clave secreta -->
        <template v-if="qrCode">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Escanea con tu app de autenticación:
            </p>
            <div class="inline-block p-2 bg-white rounded-lg" v-html="qrCode"></div>

            <template v-if="secretKey">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    O ingresa esta clave manualmente:
                </p>
                <code class="block px-3 py-2 bg-slate-100 dark:bg-slate-900 rounded-lg text-xs font-mono text-emerald-600 dark:text-emerald-400 tracking-widest break-all">
                    {{ secretKey }}
                </code>
            </template>
        </template>

        <!-- Confirmación de código -->
        <template v-if="confirming">
            <DuiLabel
                title="Código de verificación"
                required
                :error="confirmationForm.errors.code"
            >
                <DuiInput
                    v-model="confirmationForm.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    @keyup.enter="confirmTwoFactor"
                />
            </DuiLabel>
        </template>

        <!-- Códigos de recuperación -->
        <template v-if="recoveryCodes.length > 0 && !confirming">
            <div class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-3 py-2 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Códigos de recuperación</span>
                    <button
                        type="button"
                        class="text-xs text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                        @click="regenerateRecoveryCodes"
                    >
                        <i class="mdi mdi-refresh mr-1"></i>Regenerar
                    </button>
                </div>
                <div class="p-3 grid grid-cols-1 gap-1">
                    <code
                        v-for="code in recoveryCodes"
                        :key="code"
                        class="text-xs font-mono text-slate-600 dark:text-slate-300"
                    >
                        {{ code }}
                    </code>
                </div>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500">
                Guarda estos códigos en un lugar seguro. Son de un solo uso.
            </p>
        </template>

        <!-- Acciones -->
        <div class="flex flex-col gap-2 pt-1">
            <!-- Pendiente de confirmación: mostrar confirmar/cancelar -->
            <template v-if="confirming">
                <DuiButton
                    color="primary"
                    block
                    :loading="confirmationForm.processing"
                    :disabled="confirmationForm.processing"
                    @click="confirmTwoFactor"
                >
                    Confirmar activación
                </DuiButton>
                <DuiButton
                    variant="ghost"
                    color="neutral"
                    block
                    :disabled="disabling"
                    @click="disableTwoFactor"
                >
                    Cancelar
                </DuiButton>
            </template>

            <!-- 2FA activo y confirmado -->
            <template v-else-if="twoFactorEnabled">
                <DuiButton
                    color="danger"
                    variant="outline"
                    block
                    :loading="disabling"
                    :disabled="disabling"
                    @click="disableTwoFactor"
                >
                    <i class="mdi mdi-shield-off-outline mr-1.5"></i>
                    Desactivar 2FA
                </DuiButton>
            </template>

            <!-- 2FA inactivo -->
            <template v-else>
                <DuiButton
                    color="primary"
                    block
                    :loading="enabling"
                    :disabled="enabling"
                    @click="enableTwoFactor"
                >
                    <i class="mdi mdi-shield-plus-outline mr-1.5"></i>
                    Activar 2FA
                </DuiButton>
            </template>
        </div>

    </div>
</template>
