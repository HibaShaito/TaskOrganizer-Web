/*Making our menu on ipads clickable and making close */
const bar=document.getElementById('bar');
const close=document.getElementById('close');
const nav =document.getElementById('navbar');

if (bar) {
    bar.addEventListener('click', () => {
    console.log('Bar clicked'); // Debugging
    nav.classList.add('active');
    });
}
if (close) {
    close.addEventListener('click', () => {
    console.log('Close clicked'); // Debugging
    nav.classList.remove('active');
    });
}