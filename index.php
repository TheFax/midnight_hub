<?php
$data = json_decode(file_get_contents('data.json'), true);

// Group the links by category
$grouped = [];
if ($data) {
    foreach ($data as $item) {
        $grouped[$item['category']][] = $item;
    }
}

// OPTIONAL: put the categories in alphabetical order
// ksort($grouped);

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midnight Hub</title>
    <link rel="icon" type="image/png" sizes="256x256" href="favicon.png">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="overlay">
        <h1 class="main-logo">Midnight Hub</h1>
        <div id="clock">00:00</div>
        <div id="info">Loading...</div>

        <div class="container">
            <?php if (empty($grouped)): ?>
                <p style="text-align:center;">There are no links into the database. Go to settings panel.</p>
            <?php else: ?>
                <?php foreach ($grouped as $category => $links): ?>
                    <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                    <div class="grid">
                        <?php foreach ($links as $link): ?>
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" class="card" target="_blank">
                                <i class="<?php echo htmlspecialchars($link['icon']); ?>"></i>
                                <span><?php echo htmlspecialchars($link['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <a href="admin.php" class="admin-link"><i class="fa-solid fa-gear"></i> Settings</a>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('clock').innerText = `${hours}:${minutes}`;
            document.getElementById('info').innerText = "The stars are aligned.";
        }

        updateClock();

        setInterval(updateClock, 10000); // Every 10 seconds...
    </script>
</body>

</html>