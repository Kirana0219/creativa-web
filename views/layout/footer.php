    </div>
    <footer class="footer text-dark text-center">
        <div class="container py-3">
            <p class="mb-0">&copy; <?= date('Y') ?> Creativa. All rights reserved.</p>
        </div>
    </footer>
    <?php $scriptVersion = file_exists('assets/js/script.js') ? filemtime('assets/js/script.js') : time(); ?>
    <script src="./assets/js/script.js?v=<?= $scriptVersion; ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
