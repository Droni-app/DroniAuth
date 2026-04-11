import { ref, watchEffect } from 'vue';

const isDark = ref(
    localStorage.getItem('theme') === 'dark' ||
    (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
);

watchEffect(() => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
});

// Sync when user changes system preference and has no saved choice
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        isDark.value = e.matches;
    }
});

export function useTheme() {
    const toggle = () => { isDark.value = !isDark.value; };
    return { isDark, toggle };
}
