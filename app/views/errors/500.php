<div class="error-container">
    <div class="error-content">
        <div class="error-code">500</div>
        <h1 class="error-title">Server Error</h1>
        <p class="error-message">Something went wrong on our end. We're working to fix it.</p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Go to Homepage</a>
            <a href="/contact" class="btn btn-outline">Contact Support</a>
        </div>
    </div>
</div>

<div class="error-container">
    <h1>500 - <?= $title ?></h1>
    <p><?= $message ?></p>
    <?php if (isset($exception) && DEBUG_MODE): ?>
        <div class="error-details">
            <p><strong>File:</strong> <?= $exception->getFile() ?></p>
            <p><strong>Line:</strong> <?= $exception->getLine() ?></p>
            <pre><?= $exception->getTraceAsString() ?></pre>
        </div>
    <?php endif; ?>
    <a href="/" class="btn btn-primary">Return to Homepage</a>
</div>