<?php 
include '../config/session.php'; 
include '../config/settings.php'; 
$title = "Kategoriler";

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    try {
        $deleteCategory = "DELETE FROM categories WHERE category_id = $category_id";
        if ($conn->query($deleteCategory)) {
            $_SESSION['success'] = "Kategori başarıyla silindi!";
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Kategori silinirken bir hata oluştu: " . $e->getMessage();
    }
    header("Location: kategoriler.php");
    exit;
}

$getCategories = "SELECT * FROM categories ORDER BY category_created_at DESC";
$categories = $conn->query($getCategories);

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../main.css">
    <title><?php echo $title; ?></title>
</head>
<body>
    <?php include 'navigator.php'; ?>
    <div class="relative w-[calc(100%-250px)] top-[100px] left-[250px] p-8">
        <div class="w-full">
            

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-gray-800 rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-700">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kategori Adı</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Link</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Oluşturulma Tarihi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider" style="text-align: end;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if ($categories->num_rows > 0): ?>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-white">
                                        <?php echo $category['category_name']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">
                                        <?php echo $category['category_slug']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300">
                                        <?php echo date('d.m.Y H:i', strtotime($category['category_created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm flex justify-end">
                                        <a href="kategori-duzenle.php?id=<?php echo $category['category_id']; ?>" 
                                           class="text-(--primary) hover:text-(--primary-dark) mr-3">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)" 
                                           onclick="deleteCategory(<?php echo $category['category_id']; ?>, '<?php echo $category['category_name']; ?>')"
                                           class="text-red-500 hover:text-red-600">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-300">
                                    Henüz kategori eklenmemiş.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-6">
                <h1 class="text-2xl font-bold text-white"></h1>
                <a href="kategori-ekle.php" class="bg-(--primary) text-white px-4 py-2 rounded hover:bg-(--primary-dark) transition-colors">
                    <i class="fas fa-plus mr-2"></i>Kategori Ekle
                </a>
            </div>
        </div>
    </div>

    <script>
    function deleteCategory(id, name) {
        if (confirm(`"${name}" kategorisini silmek istediğinizden emin misiniz?`)) {
            window.location.href = `kategoriler.php?delete=${id}`;
        }
    }
    </script>
</body>
</html> 