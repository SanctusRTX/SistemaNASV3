@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="d-flex align-items-center mb-4" style="gap:0.75rem;">
    <div style="width:38px;height:38px;border-radius:10px;background:rgba(52,211,153,0.12);border:1px solid rgba(52,211,153,0.25);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-upload" style="color:#34d399;font-size:1rem;"></i>
    </div>
    <div>
        <h5 class="mb-0" style="font-weight:700;font-size:1rem;color:#f1f5f9;">Subir Archivo</h5>
        <p class="mb-0" style="font-size:0.72rem;color:#64748b;">Carga archivos en el almacenamiento del servidor</p>
    </div>
</div>

<div style="max-width:600px;">
    <div style="background:rgba(15,19,28,0.8);border:1px solid rgba(255,255,255,0.06);border-radius:16px;padding:1.75rem;backdrop-filter:blur(10px);">
        <form action="javascript:void(0);" id="uploadForm">
            @csrf

            {{-- Selector de archivo --}}
            <div class="mb-4">
                <label style="font-size:0.78rem;font-weight:600;color:#94a3b8;letter-spacing:0.04em;text-transform:uppercase;">
                    <i class="fas fa-file mr-1" style="color:#34d399;"></i> Seleccionar archivo
                </label>
                <div class="custom-file mt-2">
                    <input type="file" class="custom-file-input" id="archivo" name="archivo" required>
                    <label class="custom-file-label" for="archivo">Seleccionar archivo...</label>
                </div>
                <small style="font-size:0.7rem;color:#475569;margin-top:0.5rem;display:block;">
                    <i class="fas fa-info-circle mr-1"></i> Se permiten todos los tipos de archivo.
                    <span style="color:#f87171;">Bloqueados: .php, .exe, .bat, .sh y similares.</span>
                </small>
            </div>

            {{-- Barra de progreso --}}
            <div class="d-none mb-4" id="uploadInfo">
                <div class="d-flex justify-content-between mb-2">
                    <span id="fileInfo" style="font-size:0.8rem;color:#94a3b8;">Preparando archivo...</span>
                    <span id="percentageInfo" style="font-size:0.8rem;font-weight:700;color:#3AB397;">0%</span>
                </div>
                <div style="height:6px;border-radius:4px;background:rgba(255,255,255,0.04);overflow:hidden;border:1px solid rgba(255,255,255,0.04);">
                    <div id="progressBar" style="height:100%;width:0%;border-radius:4px;background:linear-gradient(90deg,#3AB397,#10b981);transition:width 0.3s ease;"></div>
                </div>
                <div style="font-size:0.68rem;color:#475569;margin-top:0.35rem;" id="chunkInfo">Fragmento: 0/0</div>
            </div>

            {{-- Carpeta destino --}}
            <div class="mb-4">
                <label style="font-size:0.78rem;font-weight:600;color:#94a3b8;letter-spacing:0.04em;text-transform:uppercase;">
                    <i class="fas fa-folder mr-1" style="color:#3AB397;"></i> Carpeta de destino
                </label>
                <select class="form-control mt-1" id="carpeta_destino" name="carpeta_destino"
                        style="background:rgba(255,255,255,0.04)!important;border:1px solid rgba(255,255,255,0.08)!important;border-radius:10px!important;color:#f1f5f9!important;font-size:0.85rem;height:42px;">
                    <option value="">📁 Almacenamiento (Raíz)</option>
                    @foreach($todasLasCarpetas as $carpetaOpcion)
                        @php $indent = str_repeat('&nbsp;', $carpetaOpcion['level'] * 4); @endphp
                        <option value="{{ $carpetaOpcion['name'] }}">{!! $indent !!}{{ $carpetaOpcion['level'] > 0 ? '└─ ' : '' }}{{ basename($carpetaOpcion['name']) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex justify-content-between align-items-center" style="gap:0.75rem;">
                <a href="{{ route('dashboard') }}"
                   style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;border-radius:9px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;font-size:0.82rem;text-decoration:none;"
                   onmouseover="this.style.color='#f1f5f9'" onmouseout="this.style.color='#94a3b8'">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" id="submitBtn"
                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.4rem;border-radius:9px;background:linear-gradient(135deg,#34d399,#10b981);border:none;color:#07090e;font-weight:700;font-size:0.85rem;cursor:pointer;box-shadow:0 4px 14px rgba(52,211,153,0.25);">
                    <i class="fas fa-upload"></i> Subir Archivo
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('Js/chunked-uploader.js') }}"></script>
<script>
$(document).ready(function() {
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('archivo');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Por favor, selecciona un archivo para subir.');
            return false;
        }

        const file = fileInput.files[0];
        $('#uploadInfo').removeClass('d-none');
        $('#fileInfo').text('Subiendo: ' + file.name);
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');

        const uploader = new ChunkedUploader(file, {
            chunkSize: file.size > 1024 * 1024 * 1000 ? 5 * 1024 * 1024 : 2 * 1024 * 1024,
            uploadUrl: "{{ route('subir.chunk') }}",
            onProgress: function(progress) {
                document.getElementById('progressBar').style.width = progress.percentage + '%';
                $('#percentageInfo').text(progress.percentage + '%');
                $('#chunkInfo').text('Fragmento: ' + (progress.currentChunk + 1) + '/' + progress.totalChunks);
            },
            onComplete: function(response) {
                $('#fileInfo').text('¡Subida completada!');
                $('#percentageInfo').text('100%');
                document.getElementById('progressBar').style.width = '100%';
                $('#submitBtn').html('<i class="fas fa-check"></i> Completado');
                setTimeout(function() { window.location.href = response.redirectUrl; }, 2000);
            },
            onError: function(error) {
                $('#fileInfo').text('Error: ' + error.message);
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Reintentar');
            }
        });

        uploader.start();
        return false;
    });
});
</script>
@endsection