function approveRequest(id) {
    const csrf = $('#csrf_token').val();
    $.post(
        '/controllers/admin/withdraw/approve',
        { id, csrf_token: csrf },
        function (res) {
            if (res.status === 'success') {
                $('#status-' + id).text('Approved');
                Toastify({
                    text: res.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'green',
                }).showToast();

                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();
            }
        },
        'json'
    );
}

function rejectRequest(id) {
    const csrf = $('#csrf_token').val();
    $.post(
        '/controllers/admin/withdraw/reject',
        { id, csrf_token: csrf },
        function (res) {
            if (res.status === 'success') {
                $('#status-' + id).text('Rejected');
                Toastify({
                    text: res.message,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'red',
                }).showToast();

                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                Toastify({
                    text: res.message,
                    duration: 4000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'linear-gradient(to right, #ff5f6d, #ffc371)',
                }).showToast();
            }
        },
        'json'
    );
}
