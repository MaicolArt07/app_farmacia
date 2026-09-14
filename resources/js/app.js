import Swal from 'sweetalert2';

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

