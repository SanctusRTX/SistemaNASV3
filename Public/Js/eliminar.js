/**
 * Script para manejar la funcionalidad de eliminación con confirmación
 * Este script se encarga de mostrar un modal de confirmación antes de eliminar un archivo o carpeta
 */

// Función para abrir el modal de confirmación de eliminación
function confirmarEliminar(ruta, nombre, tipo) {
    console.log("Confirmando eliminación:", tipo, ruta, nombre);
    
    // Verificar si el modal existe
    if ($("#modalConfirmarEliminar").length === 0) {
        console.error("ERROR: El modal de confirmación de eliminación no existe en el DOM");
        
        // Si no existe el modal, usar confirm() nativo como fallback
        if (confirm("¿Estás seguro de que deseas eliminar " + (tipo === "carpeta" ? "la carpeta" : "el archivo") + " '" + nombre + "'? Esta acción no se puede deshacer.")) {
            // Crear un formulario dinámico para enviar la solicitud
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "/Sistema-NASv3/Src/funciones/eliminar.php";
            
            var inputRuta = document.createElement("input");
            inputRuta.type = "hidden";
            inputRuta.name = "ruta";
            inputRuta.value = ruta;
            form.appendChild(inputRuta);
            
            var inputTipo = document.createElement("input");
            inputTipo.type = "hidden";
            inputTipo.name = "tipo";
            inputTipo.value = tipo;
            form.appendChild(inputTipo);
            
            var inputAccion = document.createElement("input");
            inputAccion.type = "hidden";
            inputAccion.name = "accion";
            inputAccion.value = "eliminar";
            form.appendChild(inputAccion);
            
            document.body.appendChild(form);
            form.submit();
        }
        return;
    }
    
    // Obtener referencias a los elementos del formulario
    const rutaInput = document.getElementById("rutaEliminar");
    const tipoInput = document.getElementById("tipoElemento");
    const nombreElemento = document.getElementById("nombreElementoEliminar");
    
    // Verificar que los elementos existan antes de usarlos
    if (!rutaInput || !tipoInput || !nombreElemento) {
        console.error("ERROR: Algunos elementos del modal de eliminación no existen en el DOM");
        alert("Error: No se pudo preparar el formulario de eliminación. Recargue la página e intente nuevamente.");
        return;
    }
    
    // Establecer valores en el formulario
    rutaInput.value = ruta || "";
    tipoInput.value = tipo || "";
    
    // Mostrar el nombre del elemento a eliminar
    nombreElemento.textContent = nombre || "Desconocido";
    
    // Actualizar el título del modal según el tipo de elemento
    const modalTitle = document.getElementById("modalConfirmarEliminarLabel");
    if (modalTitle) {
        modalTitle.textContent = "Confirmar eliminación de " + (tipo === "carpeta" ? "carpeta" : "archivo");
    }
    
    // Abrir el modal usando jQuery para mayor compatibilidad
    $("#modalConfirmarEliminar").modal("show");
}

// Configurar los botones de eliminar cuando el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    console.log("Configurando botones de eliminar...");
    configurarBotonesEliminar();
    
    // Configurar el formulario de eliminación para refrescar la página
    var formEliminar = document.getElementById("formEliminar");
    if (formEliminar) {
        formEliminar.addEventListener("submit", function(e) {
            // Guardar la ubicación actual para volver a ella después de la eliminación
            localStorage.setItem("ultimaUbicacion", window.location.href);
            console.log("Guardada ubicación actual para redirección:", window.location.href);
        });
    }
    
    // Verificar si hay un parámetro de eliminación en la URL
    if (window.location.href.indexOf("eliminado=1") > -1 || window.location.href.indexOf("error=eliminacion_fallida") > -1) {
        console.log("Detectado parámetro de eliminación en la URL");
        // Eliminar el parámetro de timestamp para evitar problemas con el historial
        setTimeout(function() {
            var url = window.location.href.replace(/&t=\d+/, "");
            history.replaceState({}, document.title, url);
        }, 1000);
    }
});

// También configurar los botones cuando la ventana termine de cargar
window.addEventListener("load", function() {
    console.log("Ventana cargada, reconfigurando botones de eliminar...");
    configurarBotonesEliminar();
    
    // Verificar si estamos en una vista de carpeta
    if (window.location.href.indexOf("carpeta=") > -1) {
        console.log("Detectada vista de carpeta, aplicando configuración especial para botones de eliminar...");
        setTimeout(configurarBotonesEliminarEspecial, 500);
    }
});

// Función para configurar los botones de eliminar
function configurarBotonesEliminar() {
    // Seleccionar todos los botones con clase btn-eliminar
    var botones = document.querySelectorAll(".btn-eliminar");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        var ruta = btn.getAttribute("data-ruta");
        var nombre = btn.getAttribute("data-nombre");
        var tipo = btn.getAttribute("data-tipo");
        
        if (ruta && nombre) {
            // Reemplazar el evento onclick existente
            btn.setAttribute("onclick", "confirmarEliminar('" + ruta + "', '" + nombre + "', '" + (tipo || "archivo") + "'); return false;");
            contador++;
        }
    }
    
    console.log("Configurados " + contador + " botones de eliminar");
}

// Función específica para configurar los botones en la vista de carpetas
function configurarBotonesEliminarEspecial() {
    // Seleccionar todos los botones con clase btn-danger o que contengan el texto "Eliminar"
    var botones = document.querySelectorAll(".btn-danger, button:contains('Eliminar')");
    var contador = 0;
    
    for (var i = 0; i < botones.length; i++) {
        var btn = botones[i];
        
        // Verificar si el botón contiene el texto "Eliminar" o tiene la clase btn-danger
        if (btn.textContent.indexOf("Eliminar") > -1 || btn.classList.contains("btn-danger")) {
            var ruta = btn.getAttribute("data-ruta");
            var nombre = btn.getAttribute("data-nombre");
            var tipo = btn.getAttribute("data-tipo");
            
            if (ruta && nombre) {
                // Reemplazar el evento onclick existente
                btn.setAttribute("onclick", "confirmarEliminar('" + ruta + "', '" + nombre + "', '" + (tipo || "archivo") + "'); return false;");
                contador++;
                
                // También añadir un evento de clic usando addEventListener para mayor compatibilidad
                (function(b, r, n, t) {
                    b.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        confirmarEliminar(r, n, t || "archivo");
                        return false;
                    });
                })(btn, ruta, nombre, tipo);
            }
        }
    }
    
    console.log("Configurados " + contador + " botones de eliminar en vista especial");
    
    // Si no se configuraron botones, intentar de nuevo en 1 segundo
    if (contador === 0) {
        console.log("No se encontraron botones de eliminar, reintentando en 1 segundo...");
        setTimeout(configurarBotonesEliminarEspecial, 1000);
    }
}

// Función auxiliar para jQuery para seleccionar elementos por texto
jQuery.expr[':'].contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};
