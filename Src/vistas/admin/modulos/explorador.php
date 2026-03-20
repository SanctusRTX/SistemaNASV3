<div class="container">
    <h1>Explorador de Carpetas</h1>
    
    <div class="row">
        <div class="col-md-12">
            <?php
            // Definir la ruta base de Almacenamiento
            $rutaBase = __DIR__ . '/../../../Almacenamiento';
            
            /**
             * Función para listar carpetas y subcarpetas recursivamente
             * @param string $directorio Directorio a listar
             * @param int $nivel Nivel de profundidad actual (para la indentación)
             * @return string HTML con la lista de carpetas y subcarpetas
             */
            function listarCarpetasRecursivas($directorio, $nivel = 0) {
                if (!is_dir($directorio)) {
                    return '<div class="alert alert-danger">El directorio no existe: ' . htmlspecialchars($directorio) . '</div>';
                }
                
                $resultado = '';
                $archivos = scandir($directorio);
                
                foreach ($archivos as $archivo) {
                    if ($archivo != '.' && $archivo != '..') {
                        $rutaCompleta = $directorio . '/' . $archivo;
                        
                        if (is_dir($rutaCompleta)) {
                            // Calcular la indentación basada en el nivel
                            $indentacion = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nivel);
                            $iconoFlecha = $nivel > 0 ? '↳ ' : '';
                            
                            // Obtener la ruta relativa desde la carpeta Almacenamiento
                            $rutaBase = realpath(__DIR__ . '/../../../Almacenamiento');
                            $rutaRelativa = str_replace($rutaBase, 'Almacenamiento', $rutaCompleta);
                            $rutaRelativa = str_replace('\\', '/', $rutaRelativa);
                            
                            // Añadir la carpeta actual a la lista
                            $resultado .= '<div class="carpeta-item" style="padding-left: ' . ($nivel * 20) . 'px;">';
                            $resultado .= '<i class="fas fa-folder text-warning mr-2"></i> ';
                            $resultado .= $indentacion . $iconoFlecha . htmlspecialchars($archivo);
                            $resultado .= ' <a href="?modulo=explorar&carpeta=' . urlencode($rutaRelativa) . '" class="btn btn-sm btn-primary ml-2">Explorar</a>';
                            $resultado .= '</div>';
                            
                            // Llamada recursiva para las subcarpetas
                            $resultado .= listarCarpetasRecursivas($rutaCompleta, $nivel + 1);
                        }
                    }
                }
                
                return $resultado;
            }
            
            // Mostrar la carpeta raíz
            echo '<div class="carpeta-item">';
            echo '<i class="fas fa-folder-open text-warning mr-2"></i> ';
            echo 'Almacenamiento (Raíz)';
            echo ' <a href="?modulo=explorar&carpeta=Almacenamiento" class="btn btn-sm btn-primary ml-2">Explorar</a>';
            echo '</div>';
            
            // Mostrar las subcarpetas
            echo listarCarpetasRecursivas($rutaBase);
            ?>
        </div>
    </div>
    
    <style>
        .carpeta-item {
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
            margin: 5px 0;
        }
        .carpeta-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</div>
