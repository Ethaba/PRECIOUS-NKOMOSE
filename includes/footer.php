<?php
// Footer template
?>
</main>
<footer class="site-footer">
	<div class="container">
		<p>&copy; <?= date('Y') ?> <?= sanitize(site_config()['restaurant']['name'] ?? 'Precious Nkomose') ?> — All rights reserved.</p>
	</div>
</footer>
<script src="assets/js/main.js"></script>
<script src="assets/js/site.js"></script>
</body>
</html>
