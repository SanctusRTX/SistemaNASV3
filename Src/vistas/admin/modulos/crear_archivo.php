<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-file-alt"></i> Crear Nuevo Archivo de Texto</h4>
        </div>
        <div class="card-body">
            <form action="/Sistema-NASv3/Src/funciones/crear_archivo_directo.php" method="post" id="createFileForm">
                <!-- Nombre del archivo -->
                <div class="form-group mb-4">
                    <label for="nombre_archivo" class="form-label"><i class="fas fa-file"></i> Nombre del archivo:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="nombre_archivo" name="nombre_archivo" placeholder="Ingrese el nombre del archivo" required>
                        <div class="input-group-append">
                            <select class="form-control" id="extension" name="extension" data-tooltip data-tooltip-type="info" data-tooltip-placement="top" title="Seleccione la extensión del archivo">
                                <option value=".txt">.txt</option>
                                <option value=".docx">.docx</option>
                                <option value=".html">.html</option>
                                <option value=".css">.css</option>
                                <option value=".js">.js</option>
                                <option value=".md">.md</option>
                                <option value=".json">.json</option>
                                <option value=".xml">.xml</option>
                                <option value=".csv">.csv</option>
                            </select>
                        </div>
                    </div>
                    <small class="form-text text-muted">Ingrese un nombre válido para el archivo sin incluir la extensión.</small>
                </div>
                
                <!-- Contenido del archivo -->
                <div class="form-group mb-4">
                    <label for="contenido" class="form-label"><i class="fas fa-edit"></i> Contenido del archivo:</label>
                    <div class="editor-options mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="editor_type" id="editor_simple" value="simple" checked data-tooltip data-tooltip-type="primary" data-tooltip-placement="top" title="Editor básico de texto plano">
                            <label class="form-check-label" for="editor_simple">Editor Simple</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="editor_type" id="editor_tiny" value="tiny" data-tooltip data-tooltip-type="success" data-tooltip-placement="top" title="Editor avanzado con formato enriquecido">
                            <label class="form-check-label" for="editor_tiny">Editor Avanzado (TinyMCE)</label>
                        </div>
                    </div>
                    <div id="editor-container">
                        <textarea class="form-control" id="contenido" name="contenido" rows="10" placeholder="Ingrese el contenido del archivo..."></textarea>
                    </div>
                    <small class="form-text text-muted">El contenido puede estar vacío si desea crear un archivo en blanco.</small>
                </div>
                
                <!-- Selección de carpeta destino -->
                <div class="form-group mb-3">
                    <label for="carpeta_destino" class="form-label"><i class="fas fa-folder"></i> Carpeta de destino:</label>
                    <select class="form-control" id="carpeta_destino" name="carpeta_destino" required data-bs-toggle="tooltip" title="Seleccione la carpeta donde se guardará el archivo">
                        <option value="Almacenamiento">Almacenamiento (Raíz)</option>
                        <?php
                        $rutaAlmacenamiento = __DIR__ . '/../../../Almacenamiento';
                        
                        // Función para listar carpetas de forma jerárquica
                        function listarCarpetasSelect($directorio, $nivel = 0, $rutaRelativaBase = '') {
                            $items = scandir($directorio);
                            $rutaBase = realpath(__DIR__ . '/../../../Almacenamiento');
                            
                            foreach ($items as $item) {
                                if ($item == '.' || $item == '..') continue;
                                
                                $rutaCompleta = $directorio . DIRECTORY_SEPARATOR . $item;
                                
                                if (is_dir($rutaCompleta)) {
                                    // Construir la ruta relativa para el valor del option
                                    $rutaRelativa = $rutaRelativaBase . ($rutaRelativaBase ? '/' : '') . $item;
                                    
                                    // Crear indentación visual
                                    $indentacion = str_repeat("&nbsp;", $nivel * 4);
                                    $prefijo = $nivel > 0 ? '└─ ' : '';
                                    
                                    echo '<option value="' . htmlspecialchars($rutaRelativa) . '">' . 
                                         $indentacion . $prefijo . htmlspecialchars($item) . 
                                         '</option>';
                                    
                                    // Llamada recursiva para subcarpetas
                                    listarCarpetasSelect($rutaCompleta, $nivel + 1, $rutaRelativa);
                                }
                            }
                        }
                        
                        // Listar todas las subcarpetas recursivamente
                        if (is_dir($rutaAlmacenamiento)) {
                            listarCarpetasSelect($rutaAlmacenamiento);
                        } else {
                            echo "<option value='' disabled>Error: Directorio no encontrado</option>";
                        }
                        ?>
                    </select>
                    <small class="form-text text-muted">Selecciona la carpeta donde deseas guardar el archivo.</small>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary" data-bs-toggle="tooltip" title="Volver al explorador de archivos"><i class="fas fa-arrow-left"></i> Volver</a>
                    <button type="submit" class="btn btn-success" id="submitBtn" data-bs-toggle="tooltip" title="Guardar y crear el nuevo archivo">
                        <i class="fas fa-save"></i> Crear Archivo
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Mensaje de éxito o error -->
    <?php
    if (isset($_GET['mensaje'])) {
        $tipo = isset($_GET['tipo']) && $_GET['tipo'] === 'exito' ? 'success' : 'danger';
        $icono = $tipo === 'success' ? 'check-circle' : 'exclamation-triangle';
        echo "<div class='alert alert-$tipo alert-dismissible fade show' role='alert'>";
        echo "<i class='fas fa-$icono'></i> " . htmlspecialchars($_GET['mensaje']);
        echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>";
        echo "<span aria-hidden='true'>&times;</span>";
        echo "</button>";
        echo "</div>";
    }
    ?>
</div>

<!-- JavaScript para mejorar la experiencia de usuario -->
<!-- Incluir TinyMCE desde CDN con API key -->
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
    
    console.log('Tooltips inicializados en crear_archivo.php');

    // Validación del nombre del archivo
    $('#nombre_archivo').on('input', function() {
        var nombre = $(this).val();
        var regex = /[\\/:*?"<>|]/g;
        if (regex.test(nombre)) {
            $(this).addClass('is-invalid');
            $('#submitBtn').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $('#submitBtn').prop('disabled', false);
        }
    });
    
    // Detectar cambio en la extensión del archivo
    $('#extension').on('change', function() {
        var extension = $(this).val();
        console.log('Extensión seleccionada:', extension);
        
        // Si es un archivo Word (.docx), activar TinyMCE automáticamente
        if (extension === '.docx') {
            console.log('Archivo Word (.docx) seleccionado, activando TinyMCE automáticamente...');
            $('#editor_tiny').prop('checked', true);
            $('#editor_simple').prop('disabled', true);
            initTinyMCE();
        } else {
            // Para otras extensiones, habilitar ambas opciones
            $('#editor_simple').prop('disabled', false);
        }
    });

    // Actualizar el contador de caracteres en el contenido
    $('#contenido').on('input', function() {
        var caracteresRestantes = 50000 - $(this).val().length;
        if (caracteresRestantes < 0) {
            $(this).val($(this).val().substring(0, 50000));
            caracteresRestantes = 0;
        }
    });

    // Variable para almacenar la instancia de TinyMCE
    var tinyInstance = null;

    // Función para inicializar TinyMCE
    function initTinyMCE() {
        tinymce.init({
            selector: '#contenido',
            height: 400,
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

    // Asegurar que el contenido de TinyMCE se transfiera al formulario antes de enviar
    $('#createFileForm').on('submit', function() {
        if (tinyInstance) {
            var content = tinyInstance.getContent();
            $('#contenido').val(content);
        }
    });
});
</script>
