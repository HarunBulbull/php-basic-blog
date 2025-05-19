<?php 
include '../config/session.php'; 
include '../config/settings.php'; 
$title = "Blog Ekle";


$getCategories = "SELECT * FROM categories ORDER BY category_name ASC";
$categories = $conn->query($getCategories);

function turkishToEnglish($str) {
    $turkish = array('ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç');
    $english = array('i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c');
    return str_replace($turkish, $english, $str);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $blog_title = $conn->real_escape_string($_POST['blog_title']);
        $blog_slug = $conn->real_escape_string($_POST['blog_slug']);
        $blog_content = $conn->real_escape_string($_POST['blog_content']);
        $blog_category_id = $conn->real_escape_string($_POST['blog_category_id']);
        $blog_meta_title = $conn->real_escape_string($_POST['blog_meta_title']);
        $blog_meta_description = $conn->real_escape_string($_POST['blog_meta_description']);
        $blog_meta_keywords = $conn->real_escape_string($_POST['blog_meta_keywords']);
        $blog_status = $conn->real_escape_string($_POST['blog_status']);
        $blog_image = $conn->real_escape_string($_POST['blog_image']);

        $insertBlog = "INSERT INTO blogs (blog_title, blog_slug, blog_content, blog_image, blog_category_id, blog_meta_title, blog_meta_description, blog_meta_keywords, blog_status) 
                      VALUES ('$blog_title', '$blog_slug', '$blog_content', '$blog_image', '$blog_category_id', '$blog_meta_title', '$blog_meta_description', '$blog_meta_keywords', '$blog_status')";

        if ($conn->query($insertBlog)) {
            $_SESSION['success'] = "Blog yazısı başarıyla eklendi!";
            echo "<script>
            window.location.href = 'bloglar.php';
            </script>";
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "Bu blog linki zaten kullanılıyor. Lütfen farklı bir link kullanın.";
        } else {
            $error = "Blog yazısı eklenirken bir hata oluştu: " . $e->getMessage();
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
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <title><?php echo $title; ?></title>
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
        }
        .upload-area {
            border: 2px dashed #4a5568;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: var(--primary);
        }
        .ql-editor {
            min-height: 200px;
            color: white;
        }
        .ql-toolbar {
            background: #2d3748;
            border-color: #4a5568 !important;
        }
        .ql-container {
            border-color: #4a5568 !important;
        }
        .ql-picker {
            color: white !important;
        }
        .ql-stroke {
            stroke: white !important;
        }
        .ql-fill {
            fill: white !important;
        }
        .ql-picker-options {
            background-color: #2d3748 !important;
        }
    </style>
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
                    <label for="blog_title" class="block text-white mb-2">Blog Başlığı</label>
                    <input type="text" id="blog_title" name="blog_title" required
                           class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                </div>

                <div>
                    <label for="blog_slug" class="block text-white mb-2">Blog Linki</label>
                    <div class="flex gap-2">
                        <input type="text" id="blog_slug" name="blog_slug" required oninput="updatePreview()"
                               class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                        <button type="button" onclick="generateSlug()" 
                                class="px-4 py-2 bg-(--primary) text-white rounded whitespace-nowrap">
                            Otomatik Oluştur
                        </button>
                    </div>
                    <p class="mt-2 text-gray-400 italic text-sm">
                        <?php echo $site_url; ?>/blog/<span id="slug-preview"></span>
                    </p>
                </div>

                <div>
                    <label for="blog_category_id" class="block text-white mb-2">Kategori</label>
                    <select id="blog_category_id" name="blog_category_id" required
                            class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none bg-gray-700">
                        <option value="">Kategori Seçin</option>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['category_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-white mb-2">Blog Resmi</label>
                    <div class="upload-area rounded-lg p-4 text-center cursor-pointer" onclick="document.getElementById('blog_image').click()">
                        <input type="file" id="blog_image" name="blog_image" accept="image/*" class="hidden" onchange="uploadImage(this)">
                        <div id="upload-placeholder" class="text-gray-400">
                            <i class="fas fa-cloud-upload-alt text-4xl mb-2"></i>
                            <p>Resim yüklemek için tıklayın veya sürükleyin</p>
                        </div>
                        <img id="image-preview" class="image-preview hidden mx-auto" alt="Önizleme">
                        <button type="button" id="remove-image" class="hidden mt-2 text-red-500 hover:text-red-600" onclick="removeImage()">
                            <i class="fas fa-times"></i> Resmi Kaldır
                        </button>
                        <div id="upload-progress" class="hidden mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="progress-bar" class="bg-(--primary) h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <p id="progress-text" class="text-sm text-gray-400 mt-1">Yükleniyor... 0%</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="blog_content" class="block text-white mb-2">Blog İçeriği</label>
                    <div id="editor"></div>
                    <input type="hidden" name="blog_content" id="blog_content">
                </div>

                <div>
                    <label for="blog_status" class="block text-white mb-2">Durum</label>
                    <select id="blog_status" name="blog_status" required
                            class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none bg-gray-700">
                        <option value="draft">Taslak</option>
                        <option value="published">Yayınla</option>
                    </select>
                </div>

                <div>
                    <label for="blog_meta_title" class="block text-white mb-2">Meta Başlık</label>
                    <input type="text" id="blog_meta_title" name="blog_meta_title"
                           class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                </div>

                <div>
                    <label for="blog_meta_description" class="block text-white mb-2">Meta Açıklama</label>
                    <textarea id="blog_meta_description" name="blog_meta_description" rows="3"
                              class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none"></textarea>
                </div>

                <div>
                    <label for="blog_meta_keywords" class="block text-white mb-2">Meta Anahtar Kelimeler</label>
                    <input type="text" id="blog_meta_keywords" name="blog_meta_keywords"
                           class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none"
                           placeholder="Virgül ile ayırarak yazın">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-(--primary) text-white px-6 py-2 rounded cursor-pointer transition-colors">
                        Blog Yazısını Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image']
            ]
        }
    });

    // Form gönderilmeden önce içeriği gizli input'a aktar
    document.querySelector('form').onsubmit = function() {
        document.getElementById('blog_content').value = quill.root.innerHTML;
    };

    function generateSlug() {
        const name = document.getElementById('blog_title').value;
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

        document.getElementById('blog_slug').value = slug;
        updatePreview();
    }

    function updatePreview() {
        const slug = document.getElementById('blog_slug').value;
        document.getElementById('slug-preview').textContent = slug;
    }

    function uploadImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const formData = new FormData();
            formData.append('file', file);

            document.getElementById('upload-progress').classList.remove('hidden');
            document.getElementById('progress-bar').style.width = '0%';
            document.getElementById('progress-text').textContent = 'Yükleniyor... 0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../upload.php', true);

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById('progress-bar').style.width = percentComplete + '%';
                    document.getElementById('progress-text').textContent = 'Yükleniyor... ' + Math.round(percentComplete) + '%';
                }
            });

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('image-preview').src = '../' + response.data.file_path;
                            document.getElementById('image-preview').classList.remove('hidden');
                            document.getElementById('upload-placeholder').classList.add('hidden');
                            document.getElementById('remove-image').classList.remove('hidden');
                            document.getElementById('upload-progress').classList.add('hidden');
                            
                            let hiddenInput = document.getElementById('blog_image_path');
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'blog_image';
                                hiddenInput.id = 'blog_image_path';
                                document.querySelector('form').appendChild(hiddenInput);
                            }
                            hiddenInput.value = response.data.file_path;
                        } else {
                            alert('Yükleme hatası: ' + response.message);
                            removeImage();
                        }
                    } catch (e) {
                        alert('Yanıt işlenirken hata oluştu');
                        removeImage();
                    }
                } else {
                    alert('Sunucu hatası: ' + xhr.status);
                    removeImage();
                }
            };

            xhr.onerror = function() {
                alert('Bağlantı hatası oluştu');
                removeImage();
            };

            xhr.send(formData);
        }
    }

    function removeImage() {
        const input = document.getElementById('blog_image');
        const placeholder = document.getElementById('upload-placeholder');
        const preview = document.getElementById('image-preview');
        const removeButton = document.getElementById('remove-image');
        const progress = document.getElementById('upload-progress');
        const hiddenInput = document.getElementById('blog_image_path');

        input.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeButton.classList.add('hidden');
        progress.classList.add('hidden');
        if (hiddenInput) {
            hiddenInput.value = '';
        }
    }

    const uploadArea = document.querySelector('.upload-area');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        uploadArea.classList.add('border-(--primary)');
    }

    function unhighlight(e) {
        uploadArea.classList.remove('border-(--primary)');
    }

    uploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            document.getElementById('blog_image').files = files;
            uploadImage(document.getElementById('blog_image'));
        }
    }

    document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</body>
</html>
