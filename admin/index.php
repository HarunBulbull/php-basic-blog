<?php 
include '../config/session.php'; 
include '../config/settings.php'; 
$title = "Dashboard";

$stats = [
    'total_posts' => $conn->query("SELECT COUNT(*) as total FROM blogs")->fetch_assoc()['total'],
    'published_posts' => $conn->query("SELECT COUNT(*) as total FROM blogs WHERE blog_status = 'published'")->fetch_assoc()['total'],
    'draft_posts' => $conn->query("SELECT COUNT(*) as total FROM blogs WHERE blog_status = 'draft'")->fetch_assoc()['total'],
    'total_categories' => $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'],
    'recent_posts' => $conn->query("SELECT b.*, c.category_name 
                                   FROM blogs b 
                                   LEFT JOIN categories c ON b.blog_category_id = c.category_id 
                                   ORDER BY b.blog_created_at DESC 
                                   LIMIT 5")->fetch_all(MYSQLI_ASSOC),
    'recent_categories' => $conn->query("SELECT c.*, COUNT(b.blog_id) as post_count 
                                       FROM categories c 
                                       LEFT JOIN blogs b ON c.category_id = b.blog_category_id 
                                       GROUP BY c.category_id 
                                       ORDER BY c.category_created_at DESC 
                                       LIMIT 5")->fetch_all(MYSQLI_ASSOC)
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $site_title; ?></title>
    <link rel="stylesheet" href="../main.css">
</head>
<body class="bg-gray-900">
    <?php include 'navigator.php'; ?>

    <div class="relative w-[calc(100%-250px)] top-[100px] left-[250px] p-8">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 mb-1">Toplam Yazı</p>
                        <h3 class="text-2xl font-bold text-white"><?php echo $stats['total_posts']; ?></h3>
                    </div>
                    <div class="bg-(--primary) bg-opacity-20 p-3 w-12 h-12 flex items-center justify-center rounded-full">
                        <i class="fas fa-file-alt text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 mb-1">Yayınlanan</p>
                        <h3 class="text-2xl font-bold text-white"><?php echo $stats['published_posts']; ?></h3>
                    </div>
                    <div class="bg-green-500 bg-opacity-20 p-3 w-12 h-12 flex items-center justify-center rounded-full">
                        <i class="fas fa-check text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 mb-1">Taslak</p>
                        <h3 class="text-2xl font-bold text-white"><?php echo $stats['draft_posts']; ?></h3>
                    </div>
                    <div class="bg-yellow-500 bg-opacity-20 p-3 w-12 h-12 flex items-center justify-center rounded-full">
                        <i class="fas fa-pencil-alt text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 mb-1">Kategoriler</p>
                        <h3 class="text-2xl font-bold text-white"><?php echo $stats['total_categories']; ?></h3>
                    </div>
                    <div class="bg-blue-500 bg-opacity-20 p-3 w-12 h-12 flex items-center justify-center rounded-full">
                        <i class="fas fa-folder text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-white">Son Yazılar</h2>
                <a href="admin/blog-ekle.php" class="bg-(--primary) text-white px-4 py-2 rounded-lg hover:bg-(--primary-hover) transition-colors">
                    <i class="fas fa-plus mr-2"></i>Yeni Yazı
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-700">
                            <th class="pb-3">Başlık</th>
                            <th class="pb-3">Kategori</th>
                            <th class="pb-3">Durum</th>
                            <th class="pb-3">Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_posts'] as $post): ?>
                        <tr class="border-b border-gray-700">
                            <td class="py-3 text-white"><?php echo $post['blog_title']; ?></td>
                            <td class="py-3 text-gray-400"><?php echo $post['category_name']; ?></td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-sm <?php echo $post['blog_status'] === 'published' ? 'bg-green-500 bg-opacity-20 text-white' : 'bg-yellow-500 bg-opacity-20 text-white'; ?>">
                                    <?php echo $post['blog_status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                </span>
                            </td>
                            <td class="py-3 text-gray-400">
                                <?php echo (new DateTime($post['blog_created_at']))->format('d.m.Y'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-white">Son Kategoriler</h2>
                <a href="admin/kategori-ekle.php" class="bg-(--primary) text-white px-4 py-2 rounded-lg hover:bg-(--primary-hover) transition-colors">
                    <i class="fas fa-plus mr-2"></i>Yeni Kategori
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-700">
                            <th class="pb-3">Kategori Adı</th>
                            <th class="pb-3">Yazı Sayısı</th>
                            <th class="pb-3">Oluşturulma Tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['recent_categories'] as $category): ?>
                        <tr class="border-b border-gray-700">
                            <td class="py-3 text-white"><?php echo $category['category_name']; ?></td>
                            <td class="py-3 text-gray-400">
                                <span class="bg-gray-700 px-2 py-1 rounded-full text-sm">
                                    <?php echo $category['post_count']; ?> yazı
                                </span>
                            </td>
                            <td class="py-3 text-gray-400">
                                <?php echo (new DateTime($category['category_created_at']))->format('d.m.Y'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html> 