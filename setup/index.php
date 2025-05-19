<?php
require_once '../config.php';

$createSettingsTable = "CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    site_title VARCHAR(255) NOT NULL,
    site_description TEXT,
    site_keywords TEXT,
    site_url VARCHAR(255),
    site_logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_fullName VARCHAR(255) NOT NULL,
    user_email VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    user_role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = [
        'site_title', 'site_description', 'site_keywords', 'site_url', 'site_logo',
        'user_fullName', 'user_email', 'user_password'
    ];
    $missing = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing[] = $field;
        }
    }
    if (count($missing) > 0) {
        $error = 'Lütfen tüm alanları doldurun.';
    } else {
        if ($conn->query($createSettingsTable) === TRUE) {
            $site_title = $conn->real_escape_string($_POST['site_title']);
            $site_description = $conn->real_escape_string($_POST['site_description']);
            $site_keywords = $conn->real_escape_string($_POST['site_keywords']);
            $site_url = $conn->real_escape_string($_POST['site_url']);
            $site_logo = isset($_POST['site_logo']) ? $conn->real_escape_string($_POST['site_logo']) : '';
            $insertSettings = "INSERT INTO settings (site_title, site_description, site_keywords, site_url, site_logo) 
                              VALUES ('$site_title', '$site_description', '$site_keywords', '$site_url', '$site_logo')";
            if ($conn->query($insertSettings) === TRUE) {
                if ($conn->query($createUsersTable) === TRUE) {
                    $user_fullName = $conn->real_escape_string($_POST['user_fullName']);
                    $user_email = $conn->real_escape_string($_POST['user_email']);
                    $user_password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
                    
                    $insertAdmin = "INSERT INTO users (user_fullName, user_email, user_password, user_role) 
                                   VALUES ('$user_fullName', '$user_email', '$user_password', 'admin')";
                    
                    if ($conn->query($insertAdmin) === TRUE) {
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Admin kullanıcısı eklenirken hata oluştu: " . $conn->error;
                    }
                } else {
                    $error = "Users tablosu oluşturulurken hata oluştu: " . $conn->error;
                }
            } else {
                $error = "Site ayarları eklenirken hata oluştu: " . $conn->error;
            }
        } else {
            $error = "Settings tablosu oluşturulurken hata oluştu: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurulum - Blog</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="../main.css">
</head>
<body class="w-full">
    <div class="w-[80%] max-w-[600px] min-w-[350px] mx-auto my-20 bg-(--secondary) rounded-md">
        <div class="p-4 bg-(--primary) rounded-t-md">
            <h3 class="clamp-h3 text-white font-bold">Blog Kurulum</h3>
        </div>
        <div class="p-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="" id="setupForm" autocomplete="off" class="text-white">
                <h4 class="clamp-h4 text-white font-bold">Site Ayarları</h4>

                <div class="flex flex-col mt-4">
                    <label for="site_title" class="form-label">Site Başlığı</label>
                    <input type="text" class="outline-none border-[1px] border-white rounded-md p-2" id="site_title" name="site_title" required>
                </div>

                <div class="flex flex-col mt-4">
                    <label for="site_description" class="form-label">Site Açıklaması</label>
                    <textarea class="outline-none border-[1px] border-white rounded-md p-2" id="site_description" name="site_description" rows="3" required></textarea>
                </div>
                           
                <div class="flex flex-col mt-4">
                    <label for="site_keywords" class="form-label">Anahtar Kelimeler</label>
                    <input type="text" class="outline-none border-[1px] border-white rounded-md p-2" id="site_keywords" name="site_keywords" required>
                </div>
                            
                <div class="flex flex-col mt-4">
                    <label for="site_url" class="form-label">Site URL</label>
                    <input type="url" class="outline-none border-[1px] border-white rounded-md p-2" id="site_url" name="site_url" required>
                </div>
                            
                <div class="flex flex-col mt-4">
                    <label for="site_logo" class="form-label">Site Logosu</label>
                    <input type="file" class="outline-none border-[1px] border-white rounded-md p-2" id="site_logo" accept="image/*" required>
                    <input type="hidden" name="site_logo" id="site_logo_path" required>
                    <div class="upload-progress">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="logo-container mt-2">
                        <img id="logo_preview" class="w-full" style="display: none;">
                    </div>
                </div>

                <h4 class="clamp-h4 text-white font-bold mt-4">Admin Hesabı</h4>

                <div class="flex flex-col mt-4">
                    <label for="user_fullName" class="form-label">Ad Soyad</label>
                    <input type="text" class="outline-none border-[1px] border-white rounded-md p-2" id="user_fullName" name="user_fullName" required>
                </div>
                            
                <div class="flex flex-col mt-4">
                    <label for="user_email" class="form-label">E-posta</label>
                    <input type="email" class="outline-none border-[1px] border-white rounded-md p-2" id="user_email" name="user_email" required>
                </div>
                            
                <div class="flex flex-col mt-4">
                    <label for="user_password" class="form-label">Şifre</label>
                    <input type="password" class="outline-none border-[1px] border-white rounded-md p-2" id="user_password" name="user_password" required>
                </div>

                <button type="submit" class="w-full mt-4 bg-(--primary) text-white font-bold p-2 rounded-md">Kurulumu Tamamla</button>
            </form>
        </div>
    </div>
    <script>
        let currentUpload = null;
        document.getElementById('site_logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadFile(file);
            }
        });

        document.getElementById('remove_logo').addEventListener('click', function() {
            document.getElementById('logo_preview').style.display = 'none';
            document.getElementById('site_logo_path').value = '';
            document.getElementById('site_logo').value = '';
            document.getElementById('remove_logo').style.display = 'none';
        });

        function uploadFile(file) {
            if (currentUpload) {
                currentUpload.abort();
            }

            const formData = new FormData();
            formData.append('file', file);

            const progressBar = document.querySelector('.progress-bar');
            const uploadProgress = document.querySelector('.upload-progress');
            uploadProgress.style.display = 'block';
            progressBar.style.width = '0%';

            currentUpload = new XMLHttpRequest();
            currentUpload.open('POST', '../upload.php', true);

            currentUpload.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                }
            });

            currentUpload.onload = function() {
                if (currentUpload.status === 200) {
                    const response = JSON.parse(currentUpload.responseText);
                    if (response.success) {
                        document.getElementById('logo_preview').src = response.data.file_path;
                        document.getElementById('logo_preview').style.display = 'block';
                        document.getElementById('site_logo_path').value = response.data.file_path;
                        document.getElementById('remove_logo').style.display = 'block';
                    } else {
                        alert('Dosya yüklenirken hata oluştu: ' + response.message);
                    }
                }
                uploadProgress.style.display = 'none';
                currentUpload = null;
            };

            currentUpload.onerror = function() {
                alert('Dosya yüklenirken hata oluştu');
                uploadProgress.style.display = 'none';
                currentUpload = null;
            };

            currentUpload.send(formData);
        }

        document.getElementById('setupForm').addEventListener('submit', function(e) {
            if (!document.getElementById('site_logo_path').value) {
                e.preventDefault();
                alert('Lütfen bir logo yükleyin.');
            }
        });
    </script>
</body>
</html> 