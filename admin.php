<?php
// Admin page password
$PASSWORD = 'secret123';

// Timeout after login (1800 seconds)
$timeout = 1800;

// Change the cookie lifetime on browser
ini_set('session.cookie_lifetime', $timeout);

// Change the session lifetime on server
ini_set('session.gc_maxlifetime', $timeout);

session_start();
$file = 'data.json';

// Login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $PASSWORD) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Wrong password";
    }
}

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['logged_in']);   // This is theorically not necessary because the session will be destroyed on the next line, but I'll do it anyway for security reason.
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Authentication
if (!isset($_SESSION['logged_in'])) {
?>
    <!DOCTYPE html>
    <html lang="it">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="256x256" href="favicon.png">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="style.css">
        <title>Login</title>
    </head>

    <body>
        <div class="overlay">
            <div class="login-box">
                <h2 style="text-align:center;">Admin Access</h2>
                <?php if (isset($error)) echo "<p style='color:red;text-align:center;'>$error</p>"; ?>
                <form method="post">
                    <input type="password" name="password" placeholder="Password..." required>
                    <button type="submit" name="login">Enter</button>
                </form>
                <div style="text-align:center; margin-top:20px;">
                    <a href="index.php" class="btn-small">Return to homepage</a>
                </div>
            </div>
        </div>
    </body>

    </html>
<?php
    exit;
}

// Data loading
$data = json_decode(file_get_contents($file), true) ?? [];

// Function for data saving (helper function)
function saveData($data, $file)
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// CRUD managing
$editMode = false;
$itemToEdit = ['id' => '', 'category' => '', 'title' => '', 'url' => '', 'icon' => 'fa-solid fa-link'];

// CRUD/Delete
if (isset($_GET['delete'])) {
    $idToDelete = $_GET['delete'];
    $data = array_filter($data, function ($item) use ($idToDelete) {
        return $item['id'] != $idToDelete;
    });
    // Re-index array
    $data = array_values($data);
    saveData($data, $file);
    header("Location: admin.php");
    exit;
}

// CRUD/Edit
if (isset($_GET['edit'])) {
    $editMode = true;
    $idToEdit = $_GET['edit'];
    foreach ($data as $item) {
        if ($item['id'] == $idToEdit) {
            $itemToEdit = $item;
            break;
        }
    }
}

// CRUD/Save (new or edit)
if (isset($_POST['save'])) {
    $newItem = [
        'id' => $_POST['id'] ? $_POST['id'] : time(), // Usa timestamp as ID on new record
        'category' => ucfirst(trim($_POST['category'])),
        'title' => trim($_POST['title']),
        'url' => trim($_POST['url']),
        'icon' => trim($_POST['icon'])
    ];

    if ($_POST['is_edit'] == '1') {
        // Update existing
        foreach ($data as &$item) {
            if ($item['id'] == $newItem['id']) {
                $item = $newItem;
                break;
            }
        }
    } else {
        // Add new item
        $data[] = $newItem;
    }

    saveData($data, $file);
    header("Location: admin.php");
    exit;
}

if (isset($_POST['update_order'])) {
    $newOrder = $_POST['order']; // Array of ID in correct order
    $orderedData = [];

    // Create a map of items for each ID
    $dataMap = [];
    foreach ($data as $item) {
        $dataMap[$item['id']] = $item;
    }

    // Rebuild the array, considering the order received
    foreach ($newOrder as $id) {
        if (isset($dataMap[$id])) {
            $orderedData[] = $dataMap[$id];
        }
    }

    saveData($orderedData, $file);
    echo json_encode(['status' => 'success']);
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Link</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body>
    <div class="overlay" style="display:block; overflow-y:auto;">
        <div class="container" style="margin: 0 auto; padding-top: 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Landing page control panel</h2>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php" class="btn-small">Return to Homepage</a>
                <a href="admin.php?logout=1" class="btn-small" style="background:#cf6679;">Logout</a>
            </div>


            <div class="admin-panel">
                <h3><?php echo $editMode ? 'Edit link' : 'Add new link'; ?></h3>
                <form method="post">
                    <input type="hidden" name="id" value="<?php echo $itemToEdit['id']; ?>">
                    <input type="hidden" name="is_edit" value="<?php echo $editMode ? '1' : '0'; ?>">

                    <label>Category (eg. News, Games, Work...)</label>
                    <input type="text" name="category" value="<?php echo htmlspecialchars($itemToEdit['category']); ?>" required list="cat-list">
                    <datalist id="cat-list">
                        <?php
                        $cats = array_unique(array_column($data, 'category'));
                        foreach ($cats as $c) echo "<option value='$c'>";
                        ?>
                    </datalist>

                    <label>Title (eg. Netflix)</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($itemToEdit['title']); ?>" required>

                    <label>URL (eg. https://netflix.com)</label>
                    <input type="url" name="url" value="<?php echo htmlspecialchars($itemToEdit['url']); ?>" required>

                    <label>FontAwesome Icon Class (eg. fa-solid fa-film)</label>
                    <small style="color:#aaa;">Find icons on <a href="https://fontawesome.com/search?ic=free-collection" target="_blank" style="color:var(--accent);">fontawesome.com</a></small>
                    <input type="text" name="icon" value="<?php echo htmlspecialchars($itemToEdit['icon']); ?>" required>

                    <button type="submit" name="save"><?php echo $editMode ? 'Update' : 'Add'; ?></button>
                    <?php if ($editMode): ?>
                        <a href="admin.php" style="display:block; text-align:center; color:#aaa; margin-top:10px;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-panel" style="margin-bottom: 50px;">
                <h3>Your actual link collection</h3>
                Use drag n drop to change order of existing elements.
                <table>
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>


                    <tbody id="sortable-list">
                        <?php foreach ($data as $item): ?>
                            <tr data-id="<?php echo $item['id']; ?>" style="cursor: move;">
                                <td><i class="fa-solid fa-grip-lines" style="margin-right:10px; opacity:0.5;"></i> <i class="<?php echo $item['icon']; ?>"></i></td>
                                <td><?php echo $item['title']; ?></td>
                                <td><?php echo $item['category']; ?></td>
                                <td>
                                    <a href="admin.php?edit=<?php echo $item['id']; ?>" class="btn-small">Edit</a>
                                    <a href="admin.php?delete=<?php echo $item['id']; ?>" class="btn-small" style="background:#cf6679;" onclick="return confirm('Are you sure?');">X</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

                <script>
                    const el = document.getElementById('sortable-list');
                    const sortable = new Sortable(el, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: function() {
                            const order = [];
                            // Recupera l'ordine degli ID dalle righe della tabella
                            document.querySelectorAll('#sortable-list tr').forEach(row => {
                                order.push(row.getAttribute('data-id'));
                            });

                            // Invia il nuovo ordine al server via AJAX
                            const formData = new FormData();
                            formData.append('update_order', '1');
                            order.forEach(id => formData.append('order[]', id));

                            fetch('admin.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Items ordered!');
                                });
                        }
                    });
                </script>

            </div>

        </div>
    </div>
</body>

</html>