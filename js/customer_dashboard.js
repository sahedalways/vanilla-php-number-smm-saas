document.addEventListener('DOMContentLoaded', function () {
    const msg = '<?php echo $msg; ?>';

    if (msg === 'logout_success') {
        Toastify({
            text: 'You have successfully logged out.',
            duration: 4000,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
        }).showToast();
    }
});
