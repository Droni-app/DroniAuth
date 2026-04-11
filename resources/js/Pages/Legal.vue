<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Legal" />

        <div class="space-y-6 text-sm text-slate-300">
            <div class="space-y-2">
                <h2 class="text-base font-semibold text-white">Política de privacidad</h2>
                <p class="leading-relaxed">Droni.co es un sitio web propiedad de Dronico, S.A.S. Empresa legalmente constituida en Colombia, con NIT 9003549513, con domicilio en la ciudad de Bogotá, Colombia. Gran parte del contenido de este sitio web es de acceso público, sin embargo, algunos servicios y contenidos son de acceso restringido y requieren de un registro previo o suscripción para su acceso. Droni.co se reserva el derecho de modificar en cualquier momento y sin previo aviso la presentación y configuración del sitio web, así como los contenidos y servicios que en él se incorporan. El usuario reconoce y acepta que en cualquier momento Droni.co pueda interrumpir, desactivar y/o cancelar cualquiera de los contenidos y/o servicios que se integran en el sitio web.</p>
            </div>

            <div class="space-y-2">
                <h2 class="text-base font-semibold text-white">Protección de datos personales</h2>
                <p class="leading-relaxed">Droni.co se compromete a proteger la privacidad de los usuarios que acceden a este sitio web y/o utilizan los servicios ofrecidos a través del mismo. Droni.co garantiza el cumplimiento de la normativa vigente en materia de protección de datos personales, reflejada en la Ley 1581 de 2012 y el Decreto 1377 de 2013. Para más información sobre el tratamiento de datos personales.</p>
            </div>

            <div class="space-y-2">
                <h2 class="text-base font-semibold text-white">Uso de cookies</h2>
                <p class="leading-relaxed">Este sitio web utiliza cookies para mejorar la experiencia de navegación del usuario. Algunas de estas cookies son esenciales para el correcto funcionamiento del sitio web, como el uso de Cookies de Sesión para gestionar la autenticación de usuarios. Droni.co no utiliza cookies publicitarias ni de terceros (salvo las de Google Analytics). El usuario puede configurar su navegador para bloquear o eliminar la instalación de cookies, sin que ello afecte la posibilidad del usuario de acceder a los contenidos.</p>
            </div>
        </div>
    </GuestLayout>
</template>
