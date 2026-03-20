/**
 * Script de depuración específico para la vista de carpetas
 * Versión 2.0 - Optimizada para reducir mensajes de error
 */

// Variable para controlar si ya se han configurado los botones
var botonesConfigurados = false;

// Función para aplicar eventos de clic directamente a los botones
function aplicarEventosDirectos() {
    // Si ya se configuraron los botones, no hacer nada
    if (botonesConfigurados) {
        return;
    }
    
    // Verificar si las funciones necesarias existen
    if (typeof abrirModalRenombrar !== "function" || typeof abrirModalRenombrarArchivo !== "function") {
        // Esperar a que las funciones estén disponibles
        setTimeout(aplicarEventosDirectos, 500);
        return;
    }
    
    // Seleccionar todos los botones que contengan la palabra "Renombrar"
    var botones = document.querySelectorAll("button");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        if (btn.textContent.indexOf("Renombrar") > -1) {
            // Obtener atributos
            var ruta = btn.getAttribute("data-ruta");
            var nombre = btn.getAttribute("data-nombre");
            var tipo = btn.getAttribute("data-tipo");
            
            if (ruta && nombre) {
                contador++;
                
                // Aplicar evento onclick directamente al elemento HTML
                if (tipo === "carpeta") {
                    btn.setAttribute("onclick", "abrirModalRenombrar('carpeta', '" + ruta + "', '" + nombre + "'); return false;");
                } else {
                    btn.setAttribute("onclick", "abrirModalRenombrarArchivo('" + ruta + "', '" + nombre + "'); return false;");
                }
            }
        }
    }
    
    if (contador > 0) {
        console.log("Configurados " + contador + " botones de renombrar");
        botonesConfigurados = true;
    } else {
        // Si no se encontraron botones, intentar nuevamente en 500ms
        setTimeout(aplicarEventosDirectos, 500);
    }
}

// Ejecutar cuando el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM cargado en carpeta-debug.js");
    
    // Verificar si estamos en una vista de carpeta
    if (window.location.href.indexOf("carpeta=") > -1) {
        console.log("Vista de carpeta detectada, esperando 1 segundo para aplicar eventos...");
        setTimeout(aplicarEventosDirectos, 1000);
    }
});

// También ejecutar cuando la ventana esté completamente cargada
window.addEventListener("load", function() {
    console.log("Ventana cargada en carpeta-debug.js");
    
    // Verificar si estamos en una vista de carpeta
    if (window.location.href.indexOf("carpeta=") > -1) {
        console.log("Vista de carpeta detectada, esperando 1 segundo para aplicar eventos...");
        setTimeout(aplicarEventosDirectos, 1000);
    }
});
