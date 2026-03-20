                    </div>
                </div>
            </div>
        </main>
        <footer class="footer p-2 text-center border-top mt-4">
            <p class="mb-0">&copy; 2025 UPTTMBI. Hecho por Britany.S. José.U. Miguel.P.</p>
        </footer>
    <!-- Cargar el controlador unificado de la barra lateral -->
    <script src="/Sistema-NASv3/Public/Js/sidebar-controller.js"></script>
    
    <!-- Inicializar tooltips de Bootstrap (compatible con ambos atributos) -->
    <script>
        $(function () {
            // Inicializar tooltips para Bootstrap 4
            $('[data-toggle="tooltip"]').tooltip({
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
            
            // También inicializar los elementos que usan data-bs-toggle="tooltip" con la API de Bootstrap 4
            $('[data-bs-toggle="tooltip"]').tooltip({
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
            
            console.log('Tooltips inicializados correctamente');
        });
    </script>
    </body>
    <script src="../Public/Js/script2.js"></script>
</html>
