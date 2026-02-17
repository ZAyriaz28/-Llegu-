/* generador-qr.js */
function generarQR() {
    const contenedor = document.getElementById("contenedorQR");
    contenedor.innerHTML = ""; 

    // 1. Detectar automáticamente la dirección de tu sitio web
    // (Ej: http://localhost/llegue o https://tusitio.com)
    const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
    
    // 2. Datos de la sesión (Esto podrías traerlo de PHP más adelante)
    const idClase = "Técnico-A1"; 
    const fecha = new Date().toISOString().split('T')[0];
    
    // 3. Crear la URL MÁGICA
    // Esta URL lleva al estudiante al archivo que procesa la asistencia
    const urlAsistencia = `${baseUrl}/procesar_qr.php?clase=${idClase}&fecha=${fecha}`;

    // Actualizar texto visual
    document.getElementById("fechaQR").innerText = new Date().toLocaleString();

    // 4. Generar el QR con la URL
    new QRCode(contenedor, {
        text: urlAsistencia, // ¡Ahora es un Link!
        width: 200,
        height: 200,
        colorDark : "#004a99",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.M
    });

    const modalQR = new bootstrap.Modal(document.getElementById('modalAsistencia'));
    modalQR.show();
}