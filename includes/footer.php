<!-- Pie de pagina comun con la firma visual de la plataforma -->
    <!-- Footer: Cierre informativo -->
    <footer class="site-footer">
        <div class="site-footer-shell">
            <p class="site-footer-title">FEDERACIÓN FUTSAL</p>

            <div class="site-footer-grid" aria-label="Informacion general de la plataforma">
                <section class="site-footer-block">
                    <h2>Sobre la plataforma</h2>
                    <p>Entorno academico para explorar estadisticas, clasificacion y evolucion competitiva de la liga.</p>
                </section>

                <section class="site-footer-block">
                    <h2>Contenido disponible</h2>
                    <p>Consulta equipos, jugadores, resultados por jornada, normativa y noticias de temporada.</p>
                </section>

                <section class="site-footer-block">
                    <h2>Uso formativo</h2>
                    <p>Proyecto docente orientado a practicas de desarrollo web, modelado XML y visualizacion de datos.</p>
                </section>
            </div>
        </div>
    </footer>
<?php
$assetPrefixFooter = isset($assetPrefix) ? (string) $assetPrefix : '';
$scriptsVersion = (string) (@filemtime(__DIR__ . '/../js/app.js') ?: time());
?>
    <script src="<?php echo e($assetPrefixFooter . 'js/app.js?v=' . $scriptsVersion); ?>"></script>
</body>
</html>

