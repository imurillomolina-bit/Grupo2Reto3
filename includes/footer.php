<!-- Pie de pagina comun con la firma visual de la plataforma -->
    <!-- Footer: Cierre informativo -->
    <footer class="site-footer">
        <p>FEDERACIÓN FUTSAL - Plataforma académica</p>
    </footer>
<?php
$assetPrefixFooter = isset($assetPrefix) ? (string) $assetPrefix : '';
$scriptsVersion = (string) (@filemtime(__DIR__ . '/../js/app.js') ?: time());
?>
    <script src="<?php echo e($assetPrefixFooter . 'js/app.js?v=' . $scriptsVersion); ?>"></script>
</body>
</html>

