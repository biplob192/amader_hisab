import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3200,
    timerProgressBar: true,
    customClass: { popup: 'app-swal-toast' },
});

document.querySelectorAll('[data-swal-toast]').forEach((message) => {
    toast.fire({
        icon: message.dataset.swalToast || 'success',
        title: message.textContent.trim(),
    });
});

document.querySelectorAll('form[data-swal-confirm]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        if (form.dataset.confirmed === 'true') return;

        event.preventDefault();

        const result = await Swal.fire({
            title: form.dataset.swalTitle || 'Are you sure?',
            text: form.dataset.swalText || 'This action cannot be undone.',
            icon: form.dataset.swalIcon || 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.swalConfirm,
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: 'app-swal-popup',
                confirmButton: 'app-swal-confirm',
                cancelButton: 'app-swal-cancel',
            },
            buttonsStyling: false,
        });

        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
});
