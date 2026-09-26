import Swal from 'sweetalert2';

window.Swal = Swal;

window.confirmAction = function ({ title = '¿Estás segura?', text = '', confirmText = 'Sí, continuar', danger = true } = {}) {
    return Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: danger ? '#dc2626' : '#2563eb',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then((result) => result.isConfirmed);
};

console.log('App initialized');
document.addEventListener('livewire:init', () => {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    Livewire.on('toast', (data) => {
        Toast.fire({
            icon: data.type,
            title: data.message
        });
    });

});

