<?php 
include '../config/session.php'; 
include '../config/settings.php'; 
$title = "Kategori Düzenle";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kategoriler.php");
    exit;
}

$category_id = (int)$_GET['id'];

$getCategory = "SELECT * FROM categories WHERE category_id = $category_id";
$category = $conn->query($getCategory)->fetch_assoc();

if (!$category) {
    header("Location: kategoriler.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $category_name = $conn->real_escape_string($_POST['category_name']);
        $category_slug = $conn->real_escape_string($_POST['category_slug']);
        $category_meta_title = $conn->real_escape_string($_POST['category_meta_title']);
        $category_meta_description = $conn->real_escape_string($_POST['category_meta_description']);
        $category_meta_keywords = $conn->real_escape_string($_POST['category_meta_keywords']);
        
            $updateCategory = "UPDATE categories SET 
                category_name = '$category_name',
                category_slug = '$category_slug',
                category_meta_title = '$category_meta_title',
                category_meta_description = '$category_meta_description',
                category_meta_keywords = '$category_meta_keywords'
                WHERE category_id = $category_id";

        if ($conn->query($updateCategory)) {
            $_SESSION['success'] = "Kategori başarıyla güncellendi!";
            echo "<script>
                window.location.href = 'kategoriler.php';
            </script>";
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Bu kategori linki zaten kullanılıyor. Lütfen farklı bir link kullanın.";
        } else {
            $error = "Kategori güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
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

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="category_name" class="block text-white mb-2">Kategori Adı</label>
                    <input type="text" id="category_name" name="category_name" required
                           value="<?php echo $category['category_name']; ?>"
                           class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                </div>

                <div>
                    <label for="category_slug" class="block text-white mb-2">Kategori Linki</label>
                    <div class="flex gap-2">
                        <input type="text" id="category_slug" name="category_slug" required oninput="updatePreview()"
                               value="<?php echo $category['category_slug']; ?>"
                               class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                        <button type="button" onclick="generateSlug()" 
                                class="px-4 py-2 bg-(--primary) text-white rounded whitespace-nowrap">
                            Otomatik Oluştur
                        </button>
                    </div>
                    <p class="mt-2 text-gray-400 italic text-sm">
                        <?php echo $site_url; ?>/kategori?slug=<span id="slug-preview"><?php echo $category['category_slug']; ?></span>
                    </p>
                </div>

                <div>
                    <label for="category_meta_title" class="block text-white mb-2">Meta Başlık</label>
                    <input type="text" id="category_meta_title" name="category_meta_title"
                           value="<?php echo $category['category_meta_title']; ?>"
                           class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                </div>

                <div>
                    <label for="category_meta_description" class="block text-white mb-2">Meta Açıklama</label>
                    <textarea id="category_meta_description" name="category_meta_description" rows="3"
                              class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none"><?php echo $category['category_meta_description']; ?></textarea>
                </div>

                <div>
                    <label for="category_meta_keywords" class="block text-white mb-2">Meta Anahtar Kelimeler</label>
                    <input type="text" id="category_meta_keywords" name="category_meta_keywords"
                           value="<?php echo $category['category_meta_keywords']; ?>"
                           class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none"
                           placeholder="Virgül ile ayırarak yazın">
                </div>

                <div class="flex justify-between items-center ">
                    <a href="kategoriler.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Geri Dön
                    </a>
                    <button type="submit" class="bg-(--primary) text-white px-6 py-2 rounded cursor-pointer transition-colors">
                        Kategoriyi Güncelle
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
    function generateSlug() {
        const name = document.getElementById('category_name').value;
        if (!name) return;

        let slug = name.toLowerCase()
            .replace(/ı/g, 'i')
            .replace(/ğ/g, 'g')
            .replace(/ü/g, 'u')
            .replace(/ş/g, 's')
            .replace(/ö/g, 'o')
            .replace(/ç/g, 'c')
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');

        document.getElementById('category_slug').value = slug;
        updatePreview();
    }

    function updatePreview() {
        const slug = document.getElementById('category_slug').value;
        document.getElementById('slug-preview').textContent = slug;
    }

    document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</body>
</html> 