<?php 
include '../config/session.php'; 
include '../config/settings.php'; 
$title = "Bloglar";

if (isset($_GET['delete'])) {
    $blog_id = $conn->real_escape_string($_GET['delete']);
    $deleteBlog = "DELETE FROM blogs WHERE blog_id = '$blog_id'";
    
    if ($conn->query($deleteBlog)) {
        $_SESSION['success'] = "Blog yazısı başarıyla silindi!";
    } else {
        $_SESSION['error'] = "Blog yazısı silinirken bir hata oluştu: " . $conn->error;
    }
    
    header("Location: bloglar.php");
    exit;
}

$getBlogs = "SELECT b.*, c.category_name 
            FROM blogs b 
            LEFT JOIN categories c ON b.blog_category_id = c.category_id 
            ORDER BY b.blog_created_at DESC";
$blogs = $conn->query($getBlogs);

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
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Resim</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Başlık</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider" style="text-align: end;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php if ($blogs->num_rows > 0): ?>
                            <?php while ($blog = $blogs->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img src="../<?php echo $blog['blog_image']; ?>" alt="<?php echo $blog['blog_title']; ?>" 
                                             class="h-12 w-12 object-cover rounded">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-white"><?php echo $blog['blog_title']; ?></div>
                                        <div class="text-sm text-gray-400"><?php echo $blog['blog_slug']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-700 text-gray-300">
                                            <?php echo $blog['category_name'] ?? 'Kategorisiz'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $blog['blog_status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $blog['blog_status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        <?php echo date('d.m.Y H:i', strtotime($blog['blog_created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex justify-end">
                                        <a href="blog-duzenle.php?id=<?php echo $blog['blog_id']; ?>" 
                                           class="text-(--primary) hover:text-opacity-80 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="deleteBlog(<?php echo $blog['blog_id']; ?>)" 
                                           class="text-red-500 hover:text-red-600">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                    Henüz blog yazısı bulunmuyor.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-6">
                <h1 class="text-2xl font-bold text-white"></h1>
                <a href="blog-ekle.php" class="bg-(--primary) text-white px-4 py-2 rounded hover:bg-opacity-90 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Yeni Blog Yazısı
                </a>
            </div>
        </div>
    </div>

    <script>
    function deleteBlog(id) {
        if (confirm('Bu blog yazısını silmek istediğinizden emin misiniz?')) {
            window.location.href = 'bloglar.php?delete=' + id;
        }
    }
    </script>
</body>
</html> 