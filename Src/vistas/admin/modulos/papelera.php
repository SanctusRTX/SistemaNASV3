<?php
/**
 * Vista de la papelera de reciclaje
 */

// Incluir las funciones de la papelera
require_once __DIR__ . '/../../../funciones/papelera.php';

// Definir la ruta base de almacenamiento
$rutaBase = realpath(__DIR__ . '/../../../Almacenamiento');

// Definir la ruta de la papelera (ahora fuera del directorio de Almacenamiento)
$rutaPapelera = realpath(__DIR__ . '/../../../Papelera');

// Obtener la lista de elementos en la papelera
$elementosPapelera = listarElementosPapelera($rutaBase);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trash-alt me-2"></i> Papelera de Reciclaje
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($elementosPapelera)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> La papelera está vacía.
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalVaciarPapelera">
                                <i class="fas fa-trash-alt me-2"></i> Vaciar Papelera
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Ubicación original</th>
                                        <th>Tamaño</th>
                                        <th>Fecha de eliminación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($elementosPapelera as $elemento): ?>
                                        <tr>
                                            <td>
                                                <?php if ($elemento['tipo'] === 'carpeta'): ?>
                                                    <i class="fas fa-folder text-warning me-2"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-file text-primary me-2"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($elemento['nombre_original']) ?>
                                            </td>
                                            <td><?= $elemento['tipo'] === 'carpeta' ? 'Carpeta' : 'Archivo' ?></td>
                                            <td><?= htmlspecialchars($elemento['ruta_original']) ?></td>
                                            <td><?= $elemento['tamano_formateado'] ?></td>
                                            <td><?= $elemento['fecha_eliminacion'] ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="confirmarRestaurar('<?= htmlspecialchars($elemento['nombre']) ?>', '<?= htmlspecialchars($elemento['nombre_original']) ?>')">
                                                        <i class="fas fa-undo"></i> Restaurar
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmarEliminarPermanente('<?= htmlspecialchars($elemento['nombre']) ?>', '<?= htmlspecialchars($elemento['nombre_original']) ?>')">
                                                        <i class="fas fa-times"></i> Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Restaurar -->
<div class="modal fade" id="modalConfirmarRestaurar" tabindex="-1" aria-labelledby="modalConfirmarRestaurarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalConfirmarRestaurarLabel">Confirmar restauración</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRestaurar" action="funciones/papelera.php" method="post">
                    <div class="mb-3">
                        <p>¿Estás seguro de que deseas restaurar este elemento a su ubicación original?</p>
                        <div id="elementoRestaurar" class="mb-3">
                            <strong>Elemento a restaurar:</strong> <span class="text-primary font-weight-bold" id="nombreElementoRestaurar">Cargando...</span>
                        </div>
                    </div>
                    <input type="hidden" id="elementoRestaurarInput" name="elemento" value="">
                    <input type="hidden" name="accion" value="restaurar">
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminar Permanente -->
<div class="modal fade" id="modalConfirmarEliminarPermanente" tabindex="-1" aria-labelledby="modalConfirmarEliminarPermanenteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarEliminarPermanenteLabel">Confirmar eliminación permanente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEliminarPermanente" action="/Sistema-NASv3/Src/funciones/papelera_simple.php" method="post">
                    <div class="mb-3">
                        <p class="text-danger fw-bold">¡ATENCIÓN! Esta acción no se puede deshacer.</p>
                        <p>¿Estás seguro de que deseas eliminar permanentemente este elemento?</p>
                        <div id="elementoEliminarPermanente" class="mb-3">
                            <strong>Elemento a eliminar:</strong> <span class="text-danger font-weight-bold" id="nombreElementoEliminarPermanente">Cargando...</span>
                        </div>
                    </div>
                    <input type="hidden" id="elementoEliminarPermanenteInput" name="elemento" value="">
                    <input type="hidden" name="accion" value="eliminar_permanente">
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar Permanentemente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Vaciar Papelera -->
<div class="modal fade" id="modalVaciarPapelera" tabindex="-1" aria-labelledby="modalVaciarPapeleraLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalVaciarPapeleraLabel">Confirmar vaciado de papelera</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formVaciarPapelera" action="funciones/papelera.php" method="post">
                    <div class="mb-3">
                        <p class="text-danger fw-bold">¡ATENCIÓN! Esta acción eliminará permanentemente todos los elementos de la papelera y no se puede deshacer.</p>
                        <p>¿Estás seguro de que deseas vaciar la papelera?</p>
                    </div>
                    <input type="hidden" name="accion" value="vaciar">
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Vaciar Papelera</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarRestaurar(elemento, nombreOriginal) {
        document.getElementById('nombreElementoRestaurar').textContent = nombreOriginal;
        document.getElementById('elementoRestaurarInput').value = elemento;
        
        // Usar jQuery para mostrar el modal (compatible con Bootstrap 4)
        $('#modalConfirmarRestaurar').modal('show');
    }
    
    function confirmarEliminarPermanente(elemento, nombreOriginal) {
        document.getElementById('nombreElementoEliminarPermanente').textContent = nombreOriginal;
        document.getElementById('elementoEliminarPermanenteInput').value = elemento;
        
        // Usar jQuery para mostrar el modal (compatible con Bootstrap 4)
        $('#modalConfirmarEliminarPermanente').modal('show');
    }
</script>
