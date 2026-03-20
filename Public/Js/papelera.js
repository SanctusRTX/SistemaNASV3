/**
 * JavaScript para manejar las operaciones de la papelera
 */

document.addEventListener('DOMContentLoaded', function() {
    // Configurar el formulario de restauración
    const formRestaurar = document.getElementById('formRestaurar');
    if (formRestaurar) {
        formRestaurar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const elemento = document.getElementById('elementoRestaurarInput').value;
            realizarAccionPapelera('restaurar', elemento);
        });
    }
    
    // Configurar el formulario de eliminación permanente
    const formEliminarPermanente = document.getElementById('formEliminarPermanente');
    if (formEliminarPermanente) {
        formEliminarPermanente.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const elemento = document.getElementById('elementoEliminarPermanenteInput').value;
            realizarAccionPapelera('eliminar_permanente', elemento);
        });
    }
    
    // Configurar el formulario de vaciar papelera
    const formVaciarPapelera = document.getElementById('formVaciarPapelera');
    if (formVaciarPapelera) {
        formVaciarPapelera.addEventListener('submit', function(e) {
            e.preventDefault();
            
            realizarAccionPapelera('vaciar');
        });
    }
    
    // Función para realizar acciones de la papelera mediante AJAX
    function realizarAccionPapelera(accion, elemento = null) {
        // Crear un objeto FormData para enviar los datos
        const formData = new FormData();
        formData.append('accion', accion);
        
        if (elemento) {
            formData.append('elemento', elemento);
        }
        
        // Mostrar un mensaje de carga
        mostrarMensaje('Procesando...', 'info');
        
        console.log('Enviando solicitud a papelera.php');
        // Realizar la solicitud AJAX usando una ruta relativa al host
        fetch('/Sistema-NASv3/Src/funciones/papelera.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.text();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);
            
            // Cerrar todos los modales
            cerrarModales();
            
            // Mostrar mensaje de éxito
            if (accion === 'restaurar') {
                mostrarMensaje('Elemento restaurado correctamente', 'success');
            } else if (accion === 'eliminar_permanente') {
                mostrarMensaje('Elemento eliminado permanentemente', 'success');
            } else if (accion === 'vaciar') {
                mostrarMensaje('Papelera vaciada correctamente', 'success');
            }
            
            // Recargar la página después de un breve retraso
            setTimeout(function() {
                window.location.reload();
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al procesar la solicitud: ' + error.message, 'error');
        });
    }
    
    // Función para cerrar todos los modales de Bootstrap
    function cerrarModales() {
        // Cerrar modales de Bootstrap 4
        $('.modal').modal('hide');
        
        // Si estás usando Bootstrap 5, también puedes usar:
        // const modals = document.querySelectorAll('.modal');
        // modals.forEach(modal => {
        //     const modalInstance = bootstrap.Modal.getInstance(modal);
        //     if (modalInstance) {
        //         modalInstance.hide();
        //     }
        // });
    }
    
    // Función para mostrar mensajes al usuario
    function mostrarMensaje(mensaje, tipo) {
        // Verificar si ya existe un contenedor de mensajes
        let mensajeContainer = document.getElementById('mensaje-container');
        
        if (!mensajeContainer) {
            // Crear el contenedor si no existe
            mensajeContainer = document.createElement('div');
            mensajeContainer.id = 'mensaje-container';
            mensajeContainer.style.position = 'fixed';
            mensajeContainer.style.top = '20px';
            mensajeContainer.style.right = '20px';
            mensajeContainer.style.zIndex = '9999';
            document.body.appendChild(mensajeContainer);
        }
        
        // Crear el elemento de alerta
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${tipo} alert-dismissible fade show`;
        alertElement.role = 'alert';
        alertElement.innerHTML = `
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        // Añadir la alerta al contenedor
        mensajeContainer.appendChild(alertElement);
        
        // Eliminar la alerta después de 5 segundos
        setTimeout(function() {
            alertElement.remove();
        }, 5000);
    }
});
