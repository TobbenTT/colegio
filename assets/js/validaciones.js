/* assets/js/validaciones.js */

document.addEventListener('DOMContentLoaded', function() {
    const inputPass = document.getElementById('inputPass');
    const tooltip = document.getElementById('password-requirements');
    const strengthBar = document.getElementById('strengthBar');

    // Verificamos que existan los elementos para evitar errores en otras páginas
    if (!inputPass || !tooltip) return;

    const reqLength = document.getElementById('req-length');
    const reqLower = document.getElementById('req-lower');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    // MOSTRAR / OCULTAR
    // Al hacer foco, mostramos el bloque
    inputPass.addEventListener('focus', () => { 
        tooltip.style.display = 'block'; 
    });

    // LÓGICA DE VALIDACIÓN
    inputPass.addEventListener('input', () => {
        const val = inputPass.value;
        let validCount = 0;

        // Función auxiliar para cambiar clases
        const check = (element, condition) => {
            if (condition) {
                element.classList.replace('invalid', 'valid');
                return 1;
            } else {
                element.classList.replace('valid', 'invalid');
                return 0;
            }
        };

        // Reglas
        validCount += check(reqLength, val.length >= 8);
        validCount += check(reqLower, /[a-z]/.test(val));
        validCount += check(reqUpper, /[A-Z]/.test(val));
        validCount += check(reqNumber, /[0-9]/.test(val));
        validCount += check(reqSpecial, /[^A-Za-z0-9]/.test(val));

        // Barra de colores
        const pct = (validCount / 5) * 100;
        strengthBar.style.width = pct + '%';

        if(pct < 40) strengthBar.style.backgroundColor = '#dc3545';      // Rojo
        else if(pct < 100) strengthBar.style.backgroundColor = '#ffc107'; // Amarillo
        else strengthBar.style.backgroundColor = '#198754';               // Verde
    });
});