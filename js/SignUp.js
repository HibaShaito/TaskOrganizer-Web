document.addEventListener("DOMContentLoaded", function () {
    // Cloud animation
    const cloud = document.querySelector('.cloud');
    
    function animateCloudParts() {
        cloud.classList.add('scaled');
        setTimeout(() => {
            cloud.classList.remove('scaled');
        }, 3000);
    }

    setInterval(animateCloudParts, 6000);
});

// Toggle password visibility function
function togglePasswordVisibility(inputId, iconElement) {
    const passwordField = document.getElementById(inputId);

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        iconElement.classList.replace('bx-hide', 'bx-show');
    } else {
        passwordField.type = 'password';
        iconElement.classList.replace('bx-show', 'bx-hide');
    }
}
