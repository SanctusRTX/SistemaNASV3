<?php
/**
 * Modales para renombrar archivos y carpetas
 * Este archivo contiene los modales que se utilizan para renombrar archivos y carpetas
 * Se incluye en todas las vistas para garantizar que estén disponibles
 */
?>

<!-- Modal para renombrar carpetas -->
<div class="modal fade" id="modalRenombrar" tabindex="-1" role="dialog" aria-labelledby="modalRenombrarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRenombrarLabel">Renombrar carpeta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formRenombrar" action="/Sistema-NASv3/Src/funciones/editar.php" method="post">
                <div class="modal-body">
                    <div id="rutaActual" class="mb-3">
                        <strong>Ruta:</strong> <span class="text-muted">Cargando...</span>
                    </div>
                    <div class="form-group">
                        <label for="nuevo_nombre">Nuevo nombre:</label>
                        <input type="text" class="form-control" id="nuevo_nombre" name="nuevo_nombre" required>
                        <input type="hidden" id="rutaRenombrar" name="ruta" value="">
                        <input type="hidden" name="accion" value="renombrar">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para renombrar archivos -->
<div class="modal fade" id="modalRenombrarArchivo" tabindex="-1" role="dialog" aria-labelledby="modalRenombrarArchivoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRenombrarArchivoLabel">Renombrar archivo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formRenombrarArchivo" action="/Sistema-NASv3/Src/funciones/editar.php" method="post">
                <div class="modal-body">
                    <div id="rutaActualArchivo" class="mb-3">
                        <strong>Archivo:</strong> <span class="text-muted">Cargando...</span>
                    </div>
                    <div class="form-group">
                        <label for="nuevo_nombre_archivo">Nuevo nombre (sin extensión):</label>
                        <input type="text" class="form-control" id="nuevo_nombre_archivo" name="nuevo_nombre" required>
                        <small class="form-text text-muted">La extensión del archivo se mantendrá automáticamente.</small>
                        <input type="hidden" id="rutaRenombrarArchivo" name="ruta" value="">
                        <input type="hidden" name="accion" value="renombrarArchivo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarEliminarLabel">Confirmar eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEliminar" action="/Sistema-NASv3/Src/funciones/papelera_simple.php" method="post">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                    </div>
                    <div id="elementoEliminar" class="mb-3">
                        <strong>Elemento a eliminar:</strong> <span class="text-danger font-weight-bold" id="nombreElementoEliminar">Cargando...</span>
                    </div>
                    <input type="hidden" id="rutaEliminar" name="ruta" value="">
                    <input type="hidden" id="tipoElemento" name="tipo" value="">
                    <input type="hidden" name="accion" value="eliminar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para verificar que los modales estén presentes -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("Verificando modales:");
    console.log("Modal de carpetas:", $("#modalRenombrar").length > 0 ? "Presente" : "No encontrado");
    console.log("Modal de archivos:", $("#modalRenombrarArchivo").length > 0 ? "Presente" : "No encontrado");
    console.log("Modal de eliminar:", $("#modalConfirmarEliminar").length > 0 ? "Presente" : "No encontrado");
});
</script>
