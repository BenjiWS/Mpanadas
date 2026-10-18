<script>
    window.addEventListener('swalmodal', event => {
        setTimeout(() => {
            Swal.fire({
                icon: event.detail.type,
                title: event.detail.title,
                text: event.detail.text,
                timer: 2500,
                confirmButtonText: 'OK',
            }).then(() => {
                if (event.detail.redirect) {
                    window.location.href = event.detail.redirect;
                }
            });
        }, 100);
    });

    window.addEventListener('swalConfirm', event => {
        setTimeout(() => {
            Swal.fire({
                title: event.detail.title,
                text: event.detail.text,
                icon: event.detail.type,
                showCancelButton: true,
                cancelButtonText: @js(__('Cancelar')),
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch(event.detail.action ?? 'destroy', { id: event.detail.id });
                }
            });
        }, 100);
    });
</script>
