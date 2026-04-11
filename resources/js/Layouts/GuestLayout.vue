<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white tracking-tight">DroniAuth</h1>
            <p class="text-slate-400 text-sm mt-1">Identity & Access Management</p>
        </div>
        <div class="w-full max-w-md">
            <DuiCard>
                <slot />
            </DuiCard>
        </div>
    </div>
</template>
