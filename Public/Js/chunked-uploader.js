/**
 * Sistema-NASv3 - Cargador de archivos por fragmentos
 * Permite subir archivos de gran tamaño dividiéndolos en fragmentos pequeños
 */

class ChunkedUploader {
    constructor(file, options = {}) {
        this.file = file;
        this.chunkSize = options.chunkSize || 2 * 1024 * 1024; // 2MB por fragmento por defecto
        this.uploadUrl = options.uploadUrl || '/Sistema-NASv3/Src/funciones/upload_chunk.php';
        this.onProgress = options.onProgress || function() {};
        this.onComplete = options.onComplete || function() {};
        this.onError = options.onError || function() {};
        this.retries = options.retries || 3;
        this.currentChunk = 0;
        this.totalChunks = Math.ceil(this.file.size / this.chunkSize);
        this.fileName = this.file.name;
        this.fileId = this.generateFileId();
        this.aborted = false;
    }

    // Generar un ID único para el archivo
    generateFileId() {
        return 'file_' + new Date().getTime() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Iniciar la subida
    start() {
        this.uploadNextChunk();
    }

    // Abortar la subida
    abort() {
        this.aborted = true;
    }

    // Subir el siguiente fragmento
    uploadNextChunk() {
        if (this.aborted) {
            return;
        }

        if (this.currentChunk >= this.totalChunks) {
            // Todos los fragmentos han sido subidos, finalizar
            this.completeUpload();
            return;
        }

        const start = this.currentChunk * this.chunkSize;
        const end = Math.min(start + this.chunkSize, this.file.size);
        const chunk = this.file.slice(start, end);

        const formData = new FormData();
        formData.append('chunk', chunk);
        formData.append('fileName', this.fileName);
        formData.append('fileId', this.fileId);
        formData.append('chunkIndex', this.currentChunk);
        formData.append('totalChunks', this.totalChunks);
        formData.append('fileSize', this.file.size);

        // Añadir el destino si está disponible
        const destinoSelect = document.getElementById('carpeta_destino');
        if (destinoSelect) {
            formData.append('carpeta_destino', destinoSelect.value);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.uploadUrl, true);
        
        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                // Calcular el progreso total considerando todos los fragmentos
                const loaded = this.currentChunk * this.chunkSize + e.loaded;
                const total = this.file.size;
                const percentage = Math.min(100, Math.round((loaded / total) * 100));
                
                this.onProgress({
                    loaded: loaded,
                    total: total,
                    percentage: percentage,
                    currentChunk: this.currentChunk,
                    totalChunks: this.totalChunks
                });
            }
        };

        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Fragmento subido correctamente, pasar al siguiente
                        this.currentChunk++;
                        this.uploadNextChunk();
                    } else {
                        // Error en el servidor
                        this.handleError(response.message || 'Error al subir el fragmento', this.currentChunk);
                    }
                } catch (e) {
                    // Error al parsear la respuesta
                    this.handleError('Error al procesar la respuesta del servidor', this.currentChunk);
                }
            } else {
                // Error HTTP
                this.handleError('Error HTTP: ' + xhr.status, this.currentChunk);
            }
        };

        xhr.onerror = () => {
            this.handleError('Error de red al subir el fragmento', this.currentChunk);
        };

        xhr.send(formData);
    }

    // Manejar errores y reintentar si es necesario
    handleError(message, chunkIndex, retryCount = 0) {
        if (retryCount < this.retries) {
            console.log(`Reintentando subida del fragmento ${chunkIndex} (intento ${retryCount + 1}/${this.retries})`);
            setTimeout(() => {
                this.uploadNextChunk();
            }, 1000 * (retryCount + 1)); // Esperar más tiempo entre cada reintento
        } else {
            this.onError({
                message: message,
                chunkIndex: chunkIndex
            });
        }
    }

    // Finalizar la subida cuando todos los fragmentos han sido subidos
    completeUpload() {
        const formData = new FormData();
        formData.append('action', 'complete');
        formData.append('fileId', this.fileId);
        formData.append('fileName', this.fileName);
        formData.append('totalChunks', this.totalChunks);
        formData.append('fileSize', this.file.size);

        // Añadir el destino si está disponible
        const destinoSelect = document.getElementById('carpeta_destino');
        if (destinoSelect) {
            formData.append('carpeta_destino', destinoSelect.value);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.uploadUrl, true);
        
        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const contentType = xhr.getResponseHeader("Content-Type");
                    if (!contentType || !contentType.includes("application/json")) {
                        throw new Error("La respuesta del servidor no es JSON");
                    }
                
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        this.onComplete(response);
                    } else {
                        this.onError({
                            message: response.message || 'Error al finalizar la subida'
                        });
                    }
                } catch (e) {
                    console.error("Respuesta inválida del servidor:", xhr.responseText);
                    this.onError({
                        message: 'Error al procesar la respuesta del servidor'
                    });
                }
            } else {
                this.onError({
                    message: 'Error HTTP: ' + xhr.status
                });
            }
        };

        xhr.onerror = () => {
            this.onError({
                message: 'Error de red al finalizar la subida'
            });
        };

        xhr.send(formData);
    }
}
