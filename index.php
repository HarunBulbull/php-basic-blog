<?php require_once 'config/settings.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $site_description; ?>">
    <meta name="keywords" content="<?php echo $site_keywords; ?>">
    <link rel="canonical" href="<?php echo $site_url; ?>">  
    <link rel="icon" href="<?php echo $site_logo; ?>">
    <title><?php echo $site_title; ?></title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mx-auto px-4 py-8 mt-[132px]">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $per_page = 6;
                    $offset = ($page - 1) * $per_page;

                    $countQuery = "SELECT COUNT(*) as total FROM blogs WHERE blog_status = 'published'";
                    $total_posts = $conn->query($countQuery)->fetch_assoc()['total'];
                    $total_pages = ceil($total_posts / $per_page);

                    $getBlogs = "SELECT b.*, c.category_name 
                                FROM blogs b 
                                LEFT JOIN categories c ON b.blog_category_id = c.category_id 
                                WHERE b.blog_status = 'published' 
                                ORDER BY b.blog_created_at DESC 
                                LIMIT ? OFFSET ?";
                    $stmt = $conn->prepare($getBlogs);
                    $stmt->bind_param("ii", $per_page, $offset);
                    $stmt->execute();
                    $blogs = $stmt->get_result();

                    if ($blogs && $blogs->num_rows > 0) {
                        while ($blog = $blogs->fetch_assoc()) {
                            $content = strip_tags($blog['blog_content']);
                            $content = substr($content, 0, 200) . '...';
                            
                            $date = new DateTime($blog['blog_created_at']);
                            $formatted_date = $date->format('d.m.Y');
                            ?>
                            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                                <div class="relative">
                                    <img src="<?php echo $blog['blog_image']; ?>" 
                                         alt="<?php echo $blog['blog_title']; ?>" 
                                         class="w-full h-48 object-cover">
                                    <div class="absolute top-4 right-4 bg-(--primary) text-white px-3 py-1 rounded-full text-sm">
                                        <?php echo $blog['category_name']; ?>
                                    </div>
                                </div>
                                <div class="p-6">
                                    <h2 class="text-xl font-bold text-white mb-2">
                                        <?php echo $blog['blog_title']; ?>
                                    </h2>
                                    <p class="text-gray-400 mb-4">
                                        <?php echo $content; ?>
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500 text-sm">
                                            <i class="far fa-calendar-alt mr-2"></i>
                                            <?php echo $formatted_date; ?>
                                        </span>
                                        <a href="/blog?slug=<?php echo $blog['blog_slug']; ?>" 
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
                        echo '<div class="col-span-full text-center text-gray-400">Henüz blog yazısı bulunmuyor.</div>';
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center">
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" 
                               class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-chevron-left mr-2"></i>Önceki
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="px-4 py-2 <?php echo $i === $page ? 'bg-(--primary) text-white' : 'bg-gray-800 text-white hover:bg-gray-700'; ?> rounded-lg transition-colors">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" 
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
                                while ($category = $categories->fetch_assoc()) {
                                    ?>
                                    <li>
                                        <a href="/kategori?slug=<?php echo $category['category_slug']; ?>" 
                                           class="flex items-center justify-between text-gray-400 hover:text-(--primary) transition-colors">
                                            <span><?php echo $category['category_name']; ?></span>
                                            <span class="bg-gray-700 px-2 py-1 rounded-full text-sm">
                                                <?php echo $category['post_count']; ?>
                                            </span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            } else {
                                echo '<li class="text-gray-400">Henüz kategori bulunmuyor.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
