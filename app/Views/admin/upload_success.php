<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Sukses</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Optional: Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md w-full">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">File Anda berhasil diunggah!</h3>

        <div class="mb-6 text-left">
            <h4 class="text-lg font-medium text-gray-700 mb-2">Detail Unggahan:</h4>
            <ul class="list-disc list-inside text-gray-600 space-y-1">
                <?php foreach ($upload_data as $item => $value) : ?>
                    <li><strong><?= htmlspecialchars($item); ?>:</strong> <?= htmlspecialchars($value); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <a href="<?= site_url('upload'); ?>" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 ease-in-out">
            Unggah File Lain!
        </a>
    </div>

</body>
</html>
