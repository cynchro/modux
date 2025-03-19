// modals.js

// Confirm Delete Modal
function showConfirmDeleteModal() {
    return new Promise((resolve) => {
        swal({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Cancelar',
                    value: false,
                    visible: true,
                    className: '',
                    closeModal: true,
                },
                confirm: {
                    text: 'Sí, eliminar',
                    value: true,
                    visible: true,
                    className: '',
                    closeModal: true,
                },
            },
            dangerMode: true,
        }).then((value) => {
            resolve(value);
        });
    });
}



// Success Modal
function showSuccessModal() {
    swal('Success', 'You clicked the <b style="color:green;">Success</b> button!', 'success');
}

// Error Modal
function showErrorModal() {
    swal('Error!', 'You clicked the <b style="color:red;">error</b> button!', 'error');
}

// Warning Modal
function showWarningModal() {
    swal('Warning!', 'You clicked the <b style="color:coral;">warning</b> button!', 'warning');
}

// Info Modal
function showInfoModal() {
    swal('Info!', 'You clicked the <b style="color:cornflowerblue;">info</b> button!', 'info');
}

// Question Modal
function showQuestionModal() {
    swal('Question!', 'You clicked the <b style="color:grey;">question</b> button!', 'question');
}

// Custom Icon Modal
function showCustomIconModal() {
    swal({
        title: 'Custom icon!',
        text: 'Alert with a custom image.',
        imageUrl: 'https://image.shutterstock.com/z/stock-vector--exclamation-mark-exclamation-mark-hazard-warning-symbol-flat-design-style-vector-eps-444778462.jpg',
        imageWidth: 200,
        imageHeight: 200,
        imageAlt: 'Custom image',
        animation: false
    });
}

// Subscribe Modal
function showSubscribeModal() {
    swal({
        title: 'Submit email to subscribe',
        input: 'email',
        inputPlaceholder: 'Example@email.xxx',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        showLoaderOnConfirm: true,
        preConfirm: (email) => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    if (email === 'example@email.com') {
                        swal.showValidationError('This email is already taken.');
                    }
                    resolve();
                }, 2000);
            });
        },
        allowOutsideClick: false
    }).then((result) => {
        if (result.value) {
            swal({
                type: 'success',
                title: 'Thank you for subscribe!',
                html: 'Submitted email: ' + result.value
            });
        }
    });
}

// Redirect Modal
function showRedirectModal() {
    swal({
        title: "Are you sure?",
        text: "You will be redirected to https://utopian.io",
        type: "warning",
        confirmButtonText: "Yes, visit link!",
        showCancelButton: true
    }).then((result) => {
        if (result.value) {
            window.location = 'https://utopian.io';
        } else if (result.dismiss === 'cancel') {
            swal('Cancelled', 'Your stay here :)', 'error');
        }
    });
}
    