<?php
/**
 * Configuración de límites para subida de archivos
 * Establece valores muy altos para permitir la subida de archivos sin límite práctico
 */

// Establecer límites para subida de archivos
ini_set('upload_max_filesize', '10000M');
ini_set('post_max_size', '10000M');
ini_set('memory_limit', '1000M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

// Asegurarse de que no haya límite de tiempo para scripts largos
set_time_limit(0);
