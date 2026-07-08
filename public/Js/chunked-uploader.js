class ChunkedUploader {
    constructor(file, options = {}) {
        this.file = file;
        this.chunkSize = options.chunkSize || 2 * 1024 * 1024;
        this.uploadUrl = options.uploadUrl || '/subir/chunk';
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

    generateFileId() {
        return 'file_' + new Date().getTime() + '_' + Math.random().toString(36).substr(2, 9);
    }

    start() {
        this.uploadNextChunk();
    }

    abort() {
        this.aborted = true;
    }

    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    uploadNextChunk() {
        if (this.aborted) return;

        if (this.currentChunk >= this.totalChunks) {
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
        formData.append('_token', this.getCsrfToken());

        const destinoSelect = document.getElementById('carpeta_destino');
        if (destinoSelect) formData.append('carpeta_destino', destinoSelect.value);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.uploadUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
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
                        this.currentChunk++;
                        this.uploadNextChunk();
                    } else {
                        this.handleError(response.message || 'Error al subir el fragmento', this.currentChunk);
                    }
                } catch (e) {
                    this.handleError('Error al procesar la respuesta del servidor', this.currentChunk);
                }
            } else {
                this.handleError('Error HTTP: ' + xhr.status, this.currentChunk);
            }
        };

        xhr.onerror = () => {
            this.handleError('Error de red al subir el fragmento', this.currentChunk);
        };

        xhr.send(formData);
    }

    handleError(message, chunkIndex, retryCount = 0) {
        if (retryCount < this.retries) {
            setTimeout(() => {
                this.uploadNextChunk();
            }, 1000 * (retryCount + 1));
        } else {
            this.onError({ message: message, chunkIndex: chunkIndex });
        }
    }

    completeUpload() {
        const formData = new FormData();
        formData.append('action', 'complete');
        formData.append('fileId', this.fileId);
        formData.append('fileName', this.fileName);
        formData.append('totalChunks', this.totalChunks);
        formData.append('fileSize', this.file.size);
        formData.append('_token', this.getCsrfToken());

        const destinoSelect = document.getElementById('carpeta_destino');
        if (destinoSelect) formData.append('carpeta_destino', destinoSelect.value);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.uploadUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        this.onComplete(response);
                    } else {
                        this.onError({ message: response.message || 'Error al finalizar la subida' });
                    }
                } catch (e) {
                    this.onError({ message: 'Error al procesar la respuesta del servidor' });
                }
            } else {
                this.onError({ message: 'Error HTTP: ' + xhr.status });
            }
        };

        xhr.onerror = () => {
            this.onError({ message: 'Error de red al finalizar la subida' });
        };

        xhr.send(formData);
    }
}