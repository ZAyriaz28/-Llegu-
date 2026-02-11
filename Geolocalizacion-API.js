/**
 * Geolocalizacion-API.js
 * Lógica de validación de ubicación para el Sistema de Asistencias INATEC
 */

// Configuración de la ubicación del centro (Ejemplo: INATEC Managua)
// Cámbialas por las coordenadas reales de tu centro
const CONFIG_UBICACION = {
    lat: 13.482575, 
    lon: -86.584485,
    radioMaximo: 50 // Metros permitidos a la redonda
};

/**
 * Función principal para validar si el alumno está en el rango permitido
 * @returns {Promise} Resuelve con true si está cerca, de lo contrario lanza error
 */
async function validarUbicacionEstudiante() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            return reject("Tu navegador no soporta geolocalización.");
        }

        const opciones = {
            enableHighAccuracy: true, // Máxima precisión (GPS)
            timeout: 5000,            // Tiempo máximo de espera
            maximumAge: 0             // No usar ubicaciones guardadas en caché
        };

        navigator.geolocation.getCurrentPosition(
            (posicion) => {
                const { latitude, longitude } = posicion.coords;
                const distancia = calcularDistanciaHaversine(
                    latitude, 
                    longitude, 
                    CONFIG_UBICACION.lat, 
                    CONFIG_UBICACION.lon
                );

                if (distancia <= CONFIG_UBICACION.radioMaximo) {
                    resolve({
                        valido: true,
                        distancia: Math.round(distancia)
                    });
                } else {
                    reject(`Estás muy lejos (${Math.round(distancia)}m). Debes estar en el centro.`);
                }
            },
            (error) => {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        reject("Debes permitir el acceso al GPS para marcar asistencia.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        reject("No se pudo determinar tu ubicación.");
                        break;
                    case error.TIMEOUT:
                        reject("Se agotó el tiempo esperando la ubicación.");
                        break;
                    default:
                        reject("Error desconocido al obtener ubicación.");
                        break;
                }
            },
            opciones
        );
    });
}

/**
 * Calcula la distancia en metros entre dos coordenadas
 */
function calcularDistanciaHaversine(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Radio de la Tierra en metros
    const p1 = lat1 * Math.PI / 180;
    const p2 = lat2 * Math.PI / 180;
    const deltaP = (lat2 - lat1) * Math.PI / 180;
    const deltaL = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(deltaP / 2) * Math.sin(deltaP / 2) +
              Math.cos(p1) * Math.cos(p2) *
              Math.sin(deltaL / 2) * Math.sin(deltaL / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}