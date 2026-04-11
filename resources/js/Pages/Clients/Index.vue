<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    clients: Array,
    flashSecret: String,
    flashClient: String,
});

// ─── Estado de paneles ───────────────────────────────────────────────────────
const showCreateForm       = ref(false);
const editingClient        = ref(null);
const deletingClient       = ref(null);
const regeneratingClient   = ref(null);
const showEditModal        = ref(false);
const showDeleteModal      = ref(false);
const showRegenerateModal  = ref(false);
const secretModal          = ref(!!props.flashSecret);
const copiedSecret         = ref(false);

// ─── Formulario de creación ──────────────────────────────────────────────────
const createForm = useForm({
    name:          '',
    grant_type:    'authorization_code',
    redirect_uris: '',
    confidential:  true,
});

const submitCreate = () => {
    createForm.post(route('clients.store'), {
        onSuccess: () => {
            showCreateForm.value = false;
            createForm.reset();
            secretModal.value = true;
        },
    });
};

// ─── Formulario de edición ────────────────────────────────────────────────────
const editForm = useForm({
    name:          '',
    redirect_uris: '',
});

const startEdit = (client) => {
    editingClient.value = client;
    editForm.name = client.name;
    editForm.redirect_uris = (client.redirect_uris ?? []).join('\n');
    showEditModal.value = true;
};

const cancelEdit = () => {
    showEditModal.value = false;
    editingClient.value = null;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('clients.update', editingClient.value.id), {
        onSuccess: () => cancelEdit(),
    });
};

// ─── Regenerar secret ─────────────────────────────────────────────────────────
const regenerateSecret = (client) => {
    showRegenerateModal.value = false;
    regeneratingClient.value = null;
    router.post(route('clients.regenerate-secret', client.id), {}, {
        onSuccess: () => { secretModal.value = true; },
    });
};

// ─── Eliminación ──────────────────────────────────────────────────────────────
const deleteForm = useForm({});

const confirmDelete = (client) => {
    deletingClient.value = client;
    showDeleteModal.value = true;
};

const submitDelete = () => {
    deleteForm.delete(route('clients.destroy', deletingClient.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingClient.value = null;
        },
    });
};

// ─── Copiar secret al portapapeles ────────────────────────────────────────────
const copySecret = () => {
    navigator.clipboard.writeText(props.flashSecret);
    copiedSecret.value = true;
    setTimeout(() => copiedSecret.value = false, 2000);
};

// ─── Tabla ────────────────────────────────────────────────────────────────────
const columns = [
    { label: 'Nombre', name: 'name' },
    { label: 'Tipo de grant', name: 'grant_type' },
    { label: 'Redirect URIs', name: 'redirect_uris' },
    { label: 'Creado', name: 'created_at' },
    { label: 'Acciones', name: 'actions' },
];

const grantLabel = (grants) => {
    if (!grants || grants.length === 0) return '—';
    if (grants.includes('authorization_code')) return 'Authorization Code';
    if (grants.includes('client_credentials')) return 'Client Credentials';
    return grants.join(', ');
};

const needsRedirect = computed(() => createForm.grant_type === 'authorization_code');
const editNeedsRedirect = computed(() => (editingClient.value?.grant_types ?? []).includes('authorization_code'));
</script>

<template>
    <Head title="Clientes OAuth" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Clientes OAuth2</h1>
                    <p class="text-slate-400 text-sm mt-1">
                        Gestiona las aplicaciones que se autentican a través de DroniAuth.
                    </p>
                </div>
                <DuiButton color="primary" @click="showCreateForm = !showCreateForm">
                    {{ showCreateForm ? 'Cancelar' : '+ Nuevo cliente' }}
                </DuiButton>
            </div>

            <!-- Formulario de creación -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <DuiCard v-if="showCreateForm" title="Nuevo cliente OAuth2">
                    <form @submit.prevent="submitCreate" class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <DuiLabel title="Nombre del cliente" required :error="createForm.errors.name">
                                <DuiInput
                                    v-model="createForm.name"
                                    placeholder="Mi aplicación"
                                    autofocus
                                    required
                                />
                            </DuiLabel>

                            <DuiLabel title="Tipo de grant" required :error="createForm.errors.grant_type">
                                <DuiSelect
                                    v-model="createForm.grant_type"
                                    :options="[
                                        { label: 'Authorization Code (apps con frontend)', value: 'authorization_code' },
                                        { label: 'Client Credentials (servidor a servidor)', value: 'client_credentials' },
                                    ]"
                                />
                            </DuiLabel>
                        </div>

                        <DuiLabel
                            v-if="needsRedirect"
                            title="URIs de redirección"
                            help-text="Una URI por línea. Ejemplo: https://miapp.com/callback"
                            required
                            :error="createForm.errors.redirect_uris"
                        >
                            <DuiTextarea
                                v-model="createForm.redirect_uris"
                                placeholder="https://miapp.com/callback"
                                :autoheight="false"
                                resize="none"
                                rows="3"
                            />
                        </DuiLabel>

                        <div class="flex items-center justify-between pt-2">
                            <DuiCheckbox v-if="needsRedirect" v-model="createForm.confidential" label="Cliente confidencial (con secret)" />
                            <span v-else />

                            <div class="flex gap-3">
                                <DuiButton type="button" variant="ghost" color="neutral" @click="showCreateForm = false">
                                    Cancelar
                                </DuiButton>
                                <DuiButton type="submit" color="primary" :loading="createForm.processing">
                                    Crear cliente
                                </DuiButton>
                            </div>
                        </div>
                    </form>
                </DuiCard>
            </Transition>

            <!-- Tabla de clientes -->
            <DuiCard v-if="clients.length > 0">
                <DuiTable :columns="columns" :rows="clients" class="mt-2">
                    <template #name="client">
                        <div>
                            <span class="font-medium text-white">{{ client.name }}</span>
                            <div v-if="client.secret" class="text-xs text-slate-400 font-mono mt-0.5 truncate max-w-48">
                                ID: {{ client.id.substring(0, 8) }}…
                            </div>
                        </div>
                    </template>

                    <template #grant_type="client">
                        <DuiBadge v-if="client.grant_types?.includes('authorization_code')" color="primary" variant="outline">
                            Auth Code
                        </DuiBadge>
                        <DuiBadge v-else-if="client.grant_types?.includes('client_credentials')" color="secondary" variant="outline">
                            Client Creds
                        </DuiBadge>
                        <span v-else class="text-slate-500 text-sm">{{ grantLabel(client.grant_types) }}</span>
                    </template>

                    <template #redirect_uris="client">
                        <div v-if="client.redirect_uris?.length" class="space-y-1">
                            <div v-for="uri in client.redirect_uris" :key="uri" class="text-xs text-slate-400 font-mono truncate max-w-64">
                                {{ uri }}
                            </div>
                        </div>
                        <span v-else class="text-slate-500 text-sm">—</span>
                    </template>

                    <template #created_at="client">
                        <span class="text-sm text-slate-400">{{ client.created_at.substring(0, 10) }}</span>
                    </template>

                    <template #actions="client">
                        <div class="flex items-center gap-2">
                            <DuiButton size="sm" variant="ghost" color="neutral" @click="startEdit(client)">
                                Editar
                            </DuiButton>
                            <DuiButton size="sm" variant="ghost" color="warning" @click="regeneratingClient = client; showRegenerateModal = true">
                                Regenerar secret
                            </DuiButton>
                            <DuiButton size="sm" variant="ghost" color="danger" @click="confirmDelete(client)">
                                Revocar
                            </DuiButton>
                        </div>
                    </template>
                </DuiTable>
            </DuiCard>

            <DuiCard v-else>
                <div class="text-center py-12 text-slate-400">
                    <p class="text-lg mb-2">No tienes clientes registrados.</p>
                    <p class="text-sm">Crea tu primer cliente OAuth2 para conectar aplicaciones a DroniAuth.</p>
                </div>
            </DuiCard>
        </div>

        <!-- Modal: Editar cliente -->
        <DuiModal v-model="showEditModal" title="Editar cliente" @close="cancelEdit">
            <form @submit.prevent="submitEdit" class="space-y-4">
                <DuiLabel title="Nombre" required :error="editForm.errors.name">
                    <DuiInput v-model="editForm.name" required />
                </DuiLabel>

                <DuiLabel
                    v-if="editNeedsRedirect"
                    title="URIs de redirección"
                    help-text="Una URI por línea"
                    :error="editForm.errors.redirect_uris"
                >
                    <DuiTextarea
                        v-model="editForm.redirect_uris"
                        :autoheight="false"
                        resize="none"
                        rows="3"
                    />
                </DuiLabel>
            </form>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <DuiButton type="button" variant="outline" color="neutral" @click="cancelEdit">
                        Cancelar
                    </DuiButton>
                    <DuiButton color="primary" :loading="editForm.processing" @click="submitEdit">
                        Guardar cambios
                    </DuiButton>
                </div>
            </template>
        </DuiModal>

        <!-- Modal: Confirmar regenerar secret -->
        <DuiModal v-model="showRegenerateModal" title="Regenerar client secret" color="warning" @close="regeneratingClient = null">
            <div class="space-y-4">
                <DuiAlert color="warning">
                    El secret actual dejará de funcionar de inmediato. Actualiza tus aplicaciones con el nuevo secret.
                </DuiAlert>
                <p class="text-sm">
                    ¿Confirmas que quieres regenerar el secret de <strong>{{ regeneratingClient?.name }}</strong>?
                </p>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <DuiButton variant="outline" color="neutral" @click="showRegenerateModal = false; regeneratingClient = null">
                        Cancelar
                    </DuiButton>
                    <DuiButton color="warning" @click="regenerateSecret(regeneratingClient)">
                        Sí, regenerar secret
                    </DuiButton>
                </div>
            </template>
        </DuiModal>

        <!-- Modal: Confirmar revocar -->
        <DuiModal v-model="showDeleteModal" title="Revocar cliente" color="danger" @close="deletingClient = null">
            <div class="space-y-4">
                <DuiAlert color="danger">
                    Las aplicaciones que usen este cliente perderán acceso inmediatamente.
                </DuiAlert>
                <p class="text-sm">
                    ¿Confirmas que quieres revocar <strong>{{ deletingClient?.name }}</strong>?
                </p>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <DuiButton variant="outline" color="neutral" @click="showDeleteModal = false; deletingClient = null">
                        Cancelar
                    </DuiButton>
                    <DuiButton color="danger" :loading="deleteForm.processing" @click="submitDelete">
                        Revocar cliente
                    </DuiButton>
                </div>
            </template>
        </DuiModal>

        <!-- Modal: Mostrar nuevo secret (solo una vez) -->
        <DuiModal v-model="secretModal" :title="`Secret de &quot;${flashClient}&quot;`" :show-close="false" :close-on-backdrop="false" :close-on-esc="false">
            <div class="space-y-4">
                <DuiAlert color="warning">
                    Este secret solo se muestra una vez. Cópialo ahora y guárdalo en un lugar seguro.
                </DuiAlert>
                <div class="bg-slate-900 rounded-lg p-4 font-mono text-sm text-green-400 break-all select-all">
                    {{ flashSecret }}
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <DuiButton variant="outline" color="neutral" @click="copySecret">
                        {{ copiedSecret ? '¡Copiado!' : 'Copiar al portapapeles' }}
                    </DuiButton>
                    <DuiButton color="primary" @click="secretModal = false">
                        Entendido, ya lo guardé
                    </DuiButton>
                </div>
            </template>
        </DuiModal>
    </AuthenticatedLayout>
</template>
