<style>
    [x-cloak] {
        display: none !important;
    }
</style>

{{-- Aplica el tema guardado antes de pintar la página para evitar parpadeos --}}
<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
    }
</script>

{{-- Store de Alpine para el modo claro/oscuro (Alpine viene incluido en Livewire, no se importa aparte) --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('theme', {
            theme: 'light',
            init() {
                this.theme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
                this.updateTheme();
            },
            toggle() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', this.theme);
                this.updateTheme();
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.theme }));
            },
            updateTheme() {
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
                document.documentElement.style.colorScheme = this.theme;
            },
        });
    });
</script>
