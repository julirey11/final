const showRegister = document.getElementById('show-register');
const showLogin = document.getElementById('show-login');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const container = document.querySelector('.container');

showRegister.addEventListener('click', () => {
    container.classList.add('register-active');
    loginForm.classList.remove('active');
    registerForm.classList.add('active');
});

showLogin.addEventListener('click', () => {
    container.classList.remove('register-active');
    registerForm.classList.remove('active');
    loginForm.classList.add('active');
});

