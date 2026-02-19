function guardarCambiosPerfil() {
    const inputNombre = document.getElementById('inputNombre');
    const nuevoNombre = inputNombre.value;
    
    if (nuevoNombre.trim() === "") {
        alert("Por favor, ingresa un nombre válido.");
        return;
    }

    // 1. Actualizar textos en la interfaz
    document.getElementById('userName').innerText = nuevoNombre.split(" ")[0]; // Saludo principal
    document.getElementById('nombrePanelDisplay').innerText = nuevoNombre; // Nombre dentro del panel

    // 2. Actualizar Avatar (Iniciales)
    const iniciales = nuevoNombre.split(" ").map(n => n[0]).join("").toUpperCase().substring(0, 2);
    const avatarPanel = document.getElementById('avatarPanel');
    const avatarInicio = document.getElementById('avatarDisplay');
    
    if(avatarPanel) avatarPanel.innerText = iniciales;
    if(avatarInicio) avatarInicio.innerText = iniciales;

    // 3. Guardar en LocalStorage para persistencia
    localStorage.setItem('estudiante_nombre', nuevoNombre);
    
    // 4. CERRAR EL PANEL (Lógica de Offcanvas)
    const panelElement = document.getElementById('panelPerfil');
    const instance = bootstrap.Offcanvas.getInstance(panelElement) || new bootstrap.Offcanvas(panelElement);
    instance.hide();

    // Opcional: Limpiar el input después de guardar
    // inputNombre.value = ""; 
}