const numberButtons = document.querySelectorAll('.number-btn[data-status="disponible"]');
const selectedInput = document.getElementById('numeroSeleccionado');
const raffleForm = document.getElementById('raffleForm');
let selectedNumbers = [];

numberButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const number = button.dataset.number;

        if (selectedNumbers.includes(number)) {
            selectedNumbers = selectedNumbers.filter((item) => item !== number);
            button.classList.remove('selected');
        } else {
            selectedNumbers.push(number);
            button.classList.add('selected');
        }

        selectedNumbers.sort();
        selectedInput.value = selectedNumbers.join(', ');
        selectedInput.focus();
    });
});

if (raffleForm) {
    raffleForm.addEventListener('submit', (event) => {
        const nombre = document.getElementById('nombre').value.trim();
        const correo = document.getElementById('correo').value.trim();
        const telefono = document.getElementById('telefono').value.trim();
        const numero = selectedInput.value.trim();

        if (!nombre || !correo || !telefono || !numero) {
            event.preventDefault();
            alert('Por favor completa los campos obligatorios y selecciona uno o varios números.');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo)) {
            event.preventDefault();
            alert('Por favor ingresa un correo electrónico válido.');
        }
    });
}
