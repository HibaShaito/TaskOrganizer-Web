document.addEventListener('DOMContentLoaded', () => {
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
document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('toggle-password');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);

        // Toggle the eye icon
        togglePassword.classList.toggle('bx-show');
        togglePassword.classList.toggle('bx-hide');
    });
});
