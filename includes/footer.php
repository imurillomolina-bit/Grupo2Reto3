<!-- Pie de pagina comun con la firma visual de la plataforma -->
    <!-- Footer: Cierre informativo moderno y atractivo -->
    <footer class="site-footer">
        <div class="site-footer-shell">
            <div class="site-footer-header">
                <h1 class="site-footer-title">FEDERACIÓN FUTSAL</h1>
                <p class="site-footer-subtitle">Plataforma Integral de Estadísticas y Competiciones</p>
            </div>

            <div class="site-footer-grid" aria-label="Informacion general de la plataforma">
                <section class="site-footer-block">
                    <div class="site-footer-icon">🕐</div>
                    <h2>Horarios</h2>
                    <p><strong>Lunes - Viernes:</strong> 09:00 - 18:00</p>
                    <p><strong>Sábado:</strong> 10:00 - 14:00</p>
                    <p><strong>Domingo:</strong> Cerrado</p>
                </section>

                <section class="site-footer-block">
                    <div class="site-footer-icon">📞</div>
                    <h2>Contacto</h2>
                    <p><strong>Email:</strong> info@futsal-fed.es</p>
                    <p><strong>Teléfono:</strong> +34 91 234 5678</p>
                    <p><strong>Ubicación:</strong> Bilbao, España</p>
                </section>

                <section class="site-footer-block">
                    <div class="site-footer-icon">ℹ️</div>
                    <h2>Sobre nosotros</h2>
                    <p>Federación oficial de futsal con más de 20 años organizando competiciones de calidad y promoviendo el deporte en toda España.</p>
                </section>
            </div>

            <div class="site-footer-divider"></div>

            <div class="site-footer-bottom">
                <p class="site-footer-copy">2024-2026 Federación Futsal</p>
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

