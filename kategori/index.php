<?php 
require_once '../config/settings.php';

$category_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($category_slug)) {
    header('Location: /');
    exit;
}

$getCategory = "SELECT * FROM categories WHERE category_slug = ?";
$stmt = $conn->prepare($getCategory);
$stmt->bind_param("s", $category_slug);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    header('Location: /');
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

$countQuery = "SELECT COUNT(*) as total FROM blogs WHERE blog_category_id = ? AND blog_status = 'published'";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $category['category_id']);
$stmt->execute();
$total_posts = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $per_page);

$getPosts = "SELECT b.*, c.category_name 
            FROM blogs b 
            LEFT JOIN categories c ON b.blog_category_id = c.category_id 
            WHERE b.blog_category_id = ? AND b.blog_status = 'published' 
            ORDER BY b.blog_created_at DESC 
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($getPosts);
$stmt->bind_param("iii", $category['category_id'], $per_page, $offset);
$stmt->execute();
$posts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $category['category_meta_description'] ?? $category['category_name'] . ' kategorisindeki tüm yazılar'; ?>">
    <meta name="keywords" content="<?php echo $category['category_meta_keywords'] ?? $category['category_name']; ?>">
    <meta property="og:title" content="<?php echo $category['category_meta_title'] ?? $category['category_name']; ?>">
    <meta property="og:description" content="<?php echo $category['category_meta_description'] ?? $category['category_name'] . ' kategorisindeki tüm yazılar'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $site_url; ?>/kategori/<?php echo $category['category_slug']; ?>">
    <link rel="canonical" href="<?php echo $site_url; ?>/kategori/<?php echo $category['category_slug']; ?>">
    <link rel="stylesheet" href="main.css">
    <title><?php echo $category['category_meta_title'] ?? $category['category_name']; ?> - <?php echo $site_title; ?></title>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-[132px]">
        <div class="relative h-[100px] rounded-xl overflow-hidden mb-8">
            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-white mb-4"><?php echo $category['category_name']; ?></h1>
                    <div class="text-gray-300">
                        <a href="/" class="hover:text-white">Ana Sayfa</a>
                        <span class="mx-2">/</span>
                        <span class="text-white"><?php echo $category['category_name']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    if ($posts && $posts->num_rows > 0) {
                        while ($post = $posts->fetch_assoc()) {
                            $content = strip_tags($post['blog_content']);
                            $content = substr($content, 0, 200) . '...';
                            
                            $date = new DateTime($post['blog_created_at']);
                            $formatted_date = $date->format('d.m.Y');
                            ?>
                            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                                <div class="relative">
                                    <img src="<?php echo $post['blog_image']; ?>" 
                                         alt="<?php echo $post['blog_title']; ?>" 
                                         class="w-full h-48 object-cover">
                                </div>
                                <div class="p-6">
                                    <h2 class="text-xl font-bold text-white mb-2">
                                        <?php echo $post['blog_title']; ?>
                                    </h2>
                                    <p class="text-gray-400 mb-4">
                                        <?php echo $content; ?>
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500 text-sm">
                                            <i class="far fa-calendar-alt mr-2"></i>
                                            <?php echo $formatted_date; ?>
                                        </span>
                                        <a href="/blog?slug=<?php echo $post['blog_slug']; ?>" 
                                           class="text-(--primary) hover:text-(--primary-hover) transition-colors">
                                            Devamını Oku
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="col-span-full text-center text-gray-400">Bu kategoride henüz yazı bulunmuyor.</div>';
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center">
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?slug=<?php echo $category_slug; ?>&page=<?php echo $page - 1; ?>" 
                               class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-chevron-left mr-2"></i>Önceki
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?slug=<?php echo $category_slug; ?>&page=<?php echo $i; ?>" 
                               class="px-4 py-2 <?php echo $i === $page ? 'bg-(--primary) text-white' : 'bg-gray-800 text-white hover:bg-gray-700'; ?> rounded-lg transition-colors">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?slug=<?php echo $category_slug; ?>&page=<?php echo $page + 1; ?>" 
                               class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                Sonraki<i class="fas fa-chevron-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-[148px]">
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h2 class="text-xl font-bold text-white mb-4">Kategoriler</h2>
                        <ul class="space-y-2">
                            <?php
                            $getCategories = "SELECT c.*, COUNT(b.blog_id) as post_count 
                                            FROM categories c 
                                            LEFT JOIN blogs b ON c.category_id = b.blog_category_id 
                                            WHERE b.blog_status = 'published' 
                                            GROUP BY c.category_id 
                                            ORDER BY c.category_name ASC";
                            $categories = $conn->query($getCategories);

                            if ($categories && $categories->num_rows > 0) {
                                while ($cat = $categories->fetch_assoc()) {
                                    $isActive = $cat['category_id'] === $category['category_id'];
                                    ?>
                                    <li>
                                        <a href="/kategori?slug=<?php echo $cat['category_slug']; ?>" 
                                           class="flex items-center justify-between <?php echo $isActive ? 'text-(--primary)' : 'text-gray-400 hover:text-(--primary)'; ?> transition-colors">
                                            <span><?php echo $cat['category_name']; ?></span>
                                            <span class="bg-gray-700 px-2 py-1 rounded-full text-sm">
                                                <?php echo $cat['post_count']; ?>
                                            </span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html> 