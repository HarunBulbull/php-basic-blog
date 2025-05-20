<?php 
require_once '../config/settings.php';

$slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.php");
    exit;
}

$getBlog = "SELECT b.*, c.category_name, c.category_slug 
            FROM blogs b 
            LEFT JOIN categories c ON b.blog_category_id = c.category_id 
            WHERE b.blog_slug = '$slug' AND b.blog_status = 'published'";
$blogResult = $conn->query($getBlog);

if (!$blogResult || $blogResult->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$blog = $blogResult->fetch_assoc();

$date = new DateTime($blog['blog_created_at']);
$formatted_date = $date->format('d.m.Y');

if (!empty($blog['blog_meta_title'])) {
    $title = $blog['blog_meta_title'];
} else {
    $title = $blog['blog_title'] . " - " . $site_title;
}

$description = !empty($blog['blog_meta_description']) ? $blog['blog_meta_description'] : substr(strip_tags($blog['blog_content']), 0, 160);
$keywords = !empty($blog['blog_meta_keywords']) ? $blog['blog_meta_keywords'] : $site_keywords;

$image_url = $site_url . '/' . $blog['blog_image'];
$page_url = $site_url . '/blog.php?slug=' . $slug;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $description; ?>">
    <meta name="keywords" content="<?php echo $keywords; ?>">
    <link rel="canonical" href="<?php echo $site_url; ?>/blog.php?slug=<?php echo $slug; ?>">
    <link rel="icon" href="<?php echo $site_logo; ?>">

    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo $page_url; ?>">
    <meta property="og:title" content="<?php echo $title; ?>">
    <meta property="og:description" content="<?php echo $description; ?>">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:site_name" content="<?php echo $site_title; ?>">
    <meta property="article:published_time" content="<?php echo $blog['blog_created_at']; ?>">
    <meta property="article:section" content="<?php echo $blog['category_name']; ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $page_url; ?>">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $description; ?>">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">

    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="../main.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container mx-auto px-4 py-8 mt-[100px]">
        <article class="w-full mx-auto">
            <header class="mb-8">
                <h1 class="text-4xl font-bold text-white mb-4"><?php echo $blog['blog_title']; ?></h1>
                <div class="flex items-center text-gray-400 space-x-4">
                    <a href="/kategori?slug=<?php echo $blog['category_slug']; ?>" 
                       class="flex items-center hover:text-(--primary) transition-colors">
                        <i class="fas fa-folder mr-2"></i>
                        <?php echo $blog['category_name']; ?>
                    </a>
                    <span class="flex items-center">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <?php echo $formatted_date; ?>
                    </span>
                </div>
            </header>

            <div class="mb-8">
                <img src="../<?php echo $blog['blog_image']; ?>" 
                     alt="<?php echo $blog['blog_title']; ?>" 
                     class="w-full h-[400px] object-cover rounded-lg">
            </div>

            <div class="prose prose-invert max-w-none">
                <?php echo $blog['blog_content']; ?>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">Bu Yazıyı Paylaş</h3>
                <div class="flex space-x-4">
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($site_url . '/blog.php?slug=' . $slug); ?>&text=<?php echo urlencode($blog['blog_title']); ?>" 
                       target="_blank"
                       class="bg-[#1DA1F2] text-white px-4 py-2 rounded hover:bg-opacity-90 transition-colors">
                        <i class="fab fa-twitter mr-2"></i>
                        Twitter
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($site_url . '/blog.php?slug=' . $slug); ?>" 
                       target="_blank"
                       class="bg-[#4267B2] text-white px-4 py-2 rounded hover:bg-opacity-90 transition-colors">
                        <i class="fab fa-facebook mr-2"></i>
                        Facebook
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($site_url . '/blog.php?slug=' . $slug); ?>&title=<?php echo urlencode($blog['blog_title']); ?>" 
                       target="_blank"
                       class="bg-[#0077B5] text-white px-4 py-2 rounded hover:bg-opacity-90 transition-colors">
                        <i class="fab fa-linkedin mr-2"></i>
                        LinkedIn
                    </a>
                </div>
            </div>
        </article>
    </div>
    <?php include '../footer.php'; ?>
    <script>
    document.querySelectorAll('a[target="_blank"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            window.open(this.href, 'share', 'width=600,height=400');
        });
    });
    </script>
</body>
</html> 