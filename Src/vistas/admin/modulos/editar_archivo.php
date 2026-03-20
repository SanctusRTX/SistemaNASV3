<div class="container">
    <h1>Editar Archivo</h1>
    <?php
    // Obtener la ruta del archivo
    if (!isset($_GET['archivo'])) {
        echo '<div class="alert alert-danger">No se especificó ningún archivo para editar.</div>';
        echo '<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
        exit;
    }
    
    $rutaRelativa = $_GET['archivo'];
    $rutaBase = realpath(__DIR__ . '/../../../Almacenamiento');
    $rutaAbsoluta = $rutaBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/'));
    
    // Obtener la carpeta de origen
    $carpetaOrigen = dirname($rutaRelativa);
    if ($carpetaOrigen === '.') {
        $carpetaOrigen = '';
    }
    
    // Validar que la ruta esté dentro del directorio de almacenamiento
    if (!realpath($rutaAbsoluta) || strpos(realpath($rutaAbsoluta), $rutaBase) !== 0) {
        echo '<div class="alert alert-danger">La ruta del archivo no es válida o está fuera de la carpeta raíz.</div>';
        echo '<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
        exit;
    }
    
    // Verificar que el archivo exista y sea un archivo (no un directorio)
    if (!file_exists($rutaAbsoluta) || is_dir($rutaAbsoluta)) {
        echo '<div class="alert alert-danger">El archivo no existe o es un directorio.</div>';
        echo '<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
        exit;
    }
    
    // Verificar que sea un archivo de texto o Word
    $extension = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));
    $extensionesTexto = ['txt', 'html', 'htm', 'css', 'js', 'php', 'json', 'xml', 'md', 'csv', 'log', 'docx'];
    
    if (!in_array($extension, $extensionesTexto)) {
        echo '<div class="alert alert-warning">Este tipo de archivo no se puede editar como texto.</div>';
        echo '<a href="index.php?archivo=' . urlencode($rutaRelativa) . '" class="btn btn-primary">Volver al archivo</a>';
        exit;
    }
    
    // Determinar si es un archivo Word
    $esArchivoWord = ($extension === 'docx');
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($rutaAbsoluta);
    $nombreArchivo = basename($rutaAbsoluta);
    ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-edit"></i> Editando: <?php echo htmlspecialchars($nombreArchivo); ?></h5>
        </div>
        <div class="card-body">
            <form action="/Sistema-NASv3/Src/funciones/editar.php" method="POST">
                <input type="hidden" name="accion" value="guardar_contenido">
                <input type="hidden" name="ruta" value="<?php echo htmlspecialchars($rutaRelativa); ?>">
                <input type="hidden" name="carpeta_origen" value="<?php echo htmlspecialchars($carpetaOrigen); ?>">
                
                <div class="mb-3">
                    <label for="contenido" class="form-label"><i class="fas fa-edit"></i> Contenido:</label>
                    <div class="editor-options mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="editor_type" id="editor_simple" value="simple" <?php echo $esArchivoWord ? '' : 'checked'; ?> data-bs-toggle="tooltip" title="Editor básico de texto plano" <?php echo $esArchivoWord ? 'disabled' : ''; ?>>
                            <label class="form-check-label" for="editor_simple">Editor Simple</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="editor_type" id="editor_tiny" value="tiny" <?php echo $esArchivoWord ? 'checked' : ''; ?> data-bs-toggle="tooltip" title="Editor avanzado con formato enriquecido">
                            <label class="form-check-label" for="editor_tiny">Editor Avanzado (TinyMCE)</label>
                        </div>
                        <?php if ($esArchivoWord): ?>
                            <div class="badge bg-info text-white ml-2">Archivo Word: Se usará el editor avanzado</div>
                        <?php endif; ?>
                    </div>
                    <div id="editor-container">
                        <textarea name="contenido" id="contenido" class="form-control" rows="15"><?php echo htmlspecialchars($contenido); ?></textarea>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <?php if (!empty($carpetaOrigen)): ?>
                        <a href="index.php?carpeta=<?php echo urlencode($carpetaOrigen); ?>" class="btn btn-secondary" data-bs-toggle="tooltip" title="Volver sin guardar cambios"><i class="fas fa-times"></i> Cancelar</a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-secondary" data-bs-toggle="tooltip" title="Volver sin guardar cambios"><i class="fas fa-times"></i> Cancelar</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Guardar los cambios realizados"><i class="fas fa-save"></i> Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    textarea#contenido {
        font-family: monospace;
        font-size: 14px;
        white-space: pre;
        tab-size: 4;
    }
</style>

<!-- Incluir TinyMCE desde CDN -->
<script src="https://cdn.tiny.cloud/1/wwxbqcrvpaas80smsrthh2htuwq2vciao9woc91thkur5uo4/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<script>
$(document).ready(function() {
    // Inicializar tooltips con jQuery (compatible con Bootstrap 4)
    $('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]').tooltip({
        placement: 'top',
        trigger: 'hover',
        container: 'body',
        template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner bg-primary text-white"></div></div>'
    });
    
    console.log('Tooltips inicializados en editar_archivo.php');

    // Variable para almacenar la instancia de TinyMCE
    var tinyInstance = null;

    // Función para inicializar TinyMCE
    function initTinyMCE() {
        tinymce.init({
            selector: '#contenido',
            height: 500,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                     'bold italic forecolor backcolor | alignleft aligncenter ' +
                     'alignright alignjustify | bullist numlist outdent indent | ' +
                     'removeformat | link image media table | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            promotion: false, // Desactivar mensaje de promoción
            branding: false,  // Desactivar branding
            language: 'es',   // Idioma español
            skin: 'oxide',    // Tema claro
            setup: function(editor) {
                editor.on('init', function() {
                    console.log('TinyMCE inicializado correctamente');
                });
            }
        });
        tinyInstance = tinymce.get('contenido');
    }

    // Función para destruir TinyMCE
    function removeTinyMCE() {
        if (tinyInstance) {
            tinymce.remove('#contenido');
            tinyInstance = null;
        }
    }

    // Manejar cambio de tipo de editor
    $('input[name="editor_type"]').change(function() {
        if ($(this).val() === 'tiny') {
            console.log('Inicializando TinyMCE...');
            initTinyMCE();
        } else {
            console.log('Removiendo TinyMCE...');
            removeTinyMCE();
        }
    });
    
    // Verificar si el editor TinyMCE está seleccionado al cargar la página
    if ($('#editor_tiny').is(':checked')) {
        console.log('Editor TinyMCE seleccionado por defecto, inicializando...');
        initTinyMCE();
    }
    
    // Verificar si es un archivo Word (.docx)
    <?php if ($esArchivoWord): ?>
    console.log('Archivo Word (.docx) detectado, inicializando TinyMCE automáticamente...');
    $('#editor_tiny').prop('checked', true);
    $('#editor_simple').prop('disabled', true);
    initTinyMCE();
    <?php endif; ?>

    // Asegurar que el contenido de TinyMCE se transfiera al formulario antes de enviar
    $('form').on('submit', function() {
        if (tinyInstance) {
            var content = tinyInstance.getContent();
            $('#contenido').val(content);
        }
    });
});
</script>
