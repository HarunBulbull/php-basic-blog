<?php 
include '../config/session.php'; 
include '../config/settings.php'; 
$title = "Site Ayarları";

$user_id = $_SESSION['admin_id'];
$getUser = "SELECT * FROM users WHERE user_id = '$user_id'";
$userResult = $conn->query($getUser);

if (!$userResult) {
    die("Veritabanı sorgusu başarısız: " . $conn->error);
}

if ($userResult->num_rows === 0) {
    die("Kullanıcı bulunamadı. Lütfen tekrar giriş yapın.");
}

$user = $userResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_settings'])) {
            $site_title = $conn->real_escape_string($_POST['site_title']);
            $site_description = $conn->real_escape_string($_POST['site_description']);
            $site_keywords = $conn->real_escape_string($_POST['site_keywords']);
            $site_url = $conn->real_escape_string($_POST['site_url']);
            $site_logo = isset($_POST['site_logo']) && !empty($_POST['site_logo']) ? 
                        $conn->real_escape_string($_POST['site_logo']) : 
                        $site_logo;

            $updateSettings = "UPDATE settings SET 
                site_title = '$site_title',
                site_description = '$site_description',
                site_keywords = '$site_keywords',
                site_url = '$site_url',
                site_logo = '$site_logo'
                WHERE setting_id = 1";

            if ($conn->query($updateSettings)) {
                $_SESSION['success'] = "Site ayarları başarıyla güncellendi!";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'ayarlar.php';
                    }, 1000);
                </script>";
            }
        } elseif (isset($_POST['update_profile'])) {
            $user_fullName = $conn->real_escape_string($_POST['user_fullName']);
            $user_email = $conn->real_escape_string($_POST['user_email']);
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($user_email !== $user['user_email']) {
                $checkEmail = "SELECT user_id FROM users WHERE user_email = '$user_email' AND user_id != '$user_id'";
                if ($conn->query($checkEmail)->num_rows > 0) {
                    throw new Exception("Bu e-posta adresi zaten kullanılıyor.");
                }
            }

            if (!empty($current_password)) {
                if (!password_verify($current_password, $user['user_password'])) {
                    throw new Exception("Mevcut şifre yanlış.");
                }
                if (empty($new_password)) {
                    throw new Exception("Yeni şifre boş olamaz.");
                }
                if ($new_password !== $confirm_password) {
                    throw new Exception("Yeni şifreler eşleşmiyor.");
                }
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update = ", user_password = '$hashed_password'";
            } else {
                $password_update = "";
            }

            $updateUser = "UPDATE users SET 
                user_fullName = '$user_fullName',
                user_email = '$user_email'
                $password_update
                WHERE user_id = '$user_id'";

            if ($conn->query($updateUser)) {
                $_SESSION['success'] = "Profil bilgileri başarıyla güncellendi!";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'ayarlar.php';
                    }, 1000);
                </script>";
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-800 p-6 rounded-lg">
                    <h2 class="text-xl font-bold text-white mb-6">Site Ayarları</h2>
                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="update_settings" value="1">
                        <div class="space-y-4">
                            <div>
                                <label for="site_title" class="block text-white mb-2">Site Başlığı</label>
                                <input type="text" id="site_title" name="site_title" required
                                       value="<?php echo $site_title; ?>"
                                       class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                            </div>

                            <div>
                                <label for="site_url" class="block text-white mb-2">Site URL</label>
                                <input type="url" id="site_url" name="site_url" required
                                       value="<?php echo $site_url; ?>"
                                       class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                            </div>

                            <div>
                                <label for="site_description" class="block text-white mb-2">Site Açıklaması</label>
                                <textarea id="site_description" name="site_description" rows="3"
                                          class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none"><?php echo $site_description; ?></textarea>
                            </div>

                            <div>
                                <label for="site_keywords" class="block text-white mb-2">Anahtar Kelimeler</label>
                                <input type="text" id="site_keywords" name="site_keywords"
                                       value="<?php echo $site_keywords; ?>"
                                       class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none"
                                       placeholder="Virgül ile ayırarak yazın">
                            </div>

                            <div>
                                <label class="block text-white mb-2">Site Logo</label>
                                <div class="upload-area rounded-lg p-4 text-center cursor-pointer" onclick="document.getElementById('site_logo').click()">
                                    <input type="file" id="site_logo" name="site_logo" accept="image/*" class="hidden" onchange="uploadImage(this, 'logo')">
                                    <div id="logo-placeholder" class="text-gray-400 <?php echo $site_logo ? 'hidden' : ''; ?>">
                                        <i class="fas fa-cloud-upload-alt text-4xl mb-2"></i>
                                        <p>Logo yüklemek için tıklayın veya sürükleyin</p>
                                    </div>
                                    <img id="logo-preview" class="image-preview mx-auto <?php echo $site_logo ? '' : 'hidden'; ?>" 
                                         src="../<?php echo $site_logo; ?>" alt="Logo Önizleme">
                                    <button type="button" id="remove-logo" class="<?php echo $site_logo ? '' : 'hidden'; ?> mt-2 text-red-500 hover:text-red-600" 
                                            onclick="removeImage('logo')">
                                        <i class="fas fa-times"></i> Logoyu Kaldır
                                    </button>
                                    <div id="logo-progress" class="hidden mt-4">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div id="logo-progress-bar" class="bg-(--primary) h-2.5 rounded-full" style="width: 0%"></div>
                                        </div>
                                        <p id="logo-progress-text" class="text-sm text-gray-400 mt-1">Yükleniyor... 0%</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-(--primary) text-white px-6 py-2 rounded cursor-pointer transition-colors">
                                Site Ayarlarını Kaydet
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-gray-800 p-6 rounded-lg">
                    <h2 class="text-xl font-bold text-white mb-6">Profil Ayarları</h2>
                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="space-y-4">
                            <div>
                                <label for="user_fullName" class="block text-white mb-2">Ad Soyad</label>
                                <input type="text" id="user_fullName" name="user_fullName" required
                                       value="<?php echo $user['user_fullName']; ?>"
                                       class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                            </div>

                            <div>
                                <label for="user_email" class="block text-white mb-2">E-posta Adresi</label>
                                <input type="email" id="user_email" name="user_email" required
                                       value="<?php echo $user['user_email']; ?>"
                                       class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                            </div>

                            <div class="border-t border-gray-700 pt-4 mt-4">
                                <h3 class="text-lg font-semibold text-white mb-4">Şifre Değiştir</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="current_password" class="block text-white mb-2">Mevcut Şifre</label>
                                        <input type="password" id="current_password" name="current_password"
                                               class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                                    </div>

                                    <div>
                                        <label for="new_password" class="block text-white mb-2">Yeni Şifre</label>
                                        <input type="password" id="new_password" name="new_password"
                                               class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                                    </div>

                                    <div>
                                        <label for="confirm_password" class="block text-white mb-2">Yeni Şifre (Tekrar)</label>
                                        <input type="password" id="confirm_password" name="confirm_password"
                                               class="w-full px-4 py-2 rounded text-white border border-gray-600 focus:border-(--primary) focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-(--primary) text-white px-6 py-2 rounded cursor-pointer transition-colors">
                                Profil Bilgilerini Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function uploadImage(input, type) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const formData = new FormData();
            formData.append('file', file);

            document.getElementById(`${type}-progress`).classList.remove('hidden');
            document.getElementById(`${type}-progress-bar`).style.width = '0%';
            document.getElementById(`${type}-progress-text`).textContent = 'Yükleniyor... 0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../upload.php', true);

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById(`${type}-progress-bar`).style.width = percentComplete + '%';
                    document.getElementById(`${type}-progress-text`).textContent = 'Yükleniyor... ' + Math.round(percentComplete) + '%';
                }
            });

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById(`${type}-preview`).src = '../' + response.data.file_path;
                            document.getElementById(`${type}-preview`).classList.remove('hidden');
                            document.getElementById(`${type}-placeholder`).classList.add('hidden');
                            document.getElementById(`remove-${type}`).classList.remove('hidden');
                            document.getElementById(`${type}-progress`).classList.add('hidden');
                            
                            let hiddenInput = document.getElementById(`${type}_path`);
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = `site_${type}`;
                                hiddenInput.id = `${type}_path`;
                                document.querySelector('form').appendChild(hiddenInput);
                            }
                            hiddenInput.value = response.data.file_path;
                        } else {
                            alert('Yükleme hatası: ' + response.message);
                            removeImage(type);
                        }
                    } catch (e) {
                        alert('Yanıt işlenirken hata oluştu');
                        removeImage(type);
                    }
                } else {
                    alert('Sunucu hatası: ' + xhr.status);
                    removeImage(type);
                }
            };

            xhr.onerror = function() {
                alert('Bağlantı hatası oluştu');
                removeImage(type);
            };

            xhr.send(formData);
        }
    }

    function removeImage(type) {
        const input = document.getElementById(`site_${type}`);
        const placeholder = document.getElementById(`${type}-placeholder`);
        const preview = document.getElementById(`${type}-preview`);
        const removeButton = document.getElementById(`remove-${type}`);
        const progress = document.getElementById(`${type}-progress`);
        const hiddenInput = document.getElementById(`${type}_path`);

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

    const uploadAreas = document.querySelectorAll('.upload-area');
    
    uploadAreas.forEach(area => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, () => highlight(area), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, () => unhighlight(area), false);
        });

        area.addEventListener('drop', handleDrop, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(area) {
        area.classList.add('border-(--primary)');
    }

    function unhighlight(area) {
        area.classList.remove('border-(--primary)');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        const type = e.target.closest('.upload-area').querySelector('input[type="file"]').id.replace('site_', '');
        
        if (files.length > 0) {
            document.getElementById(`site_${type}`).files = files;
            uploadImage(document.getElementById(`site_${type}`), type);
        }
    }
    </script>
</body>
</html> 