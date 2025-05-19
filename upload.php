<?php
require_once 'config/config.php';

$createUploadsTable = "CREATE TABLE IF NOT EXISTS uploads (
    upload_id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($createUploadsTable);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $original_name = $file['name'];
        $file_type = $file['type'];
        $file_size = $file['size'];
        $temp_path = $file['tmp_name'];
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_file_name = bin2hex(random_bytes(16)) . '_' . time() . '.' . $file_extension;
        
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_path = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($temp_path, $file_path)) {
            $original_name = $conn->real_escape_string($original_name);
            $new_file_name = $conn->real_escape_string($new_file_name);
            $file_path = $conn->real_escape_string($file_path);
            $file_type = $conn->real_escape_string($file_type);
            
            $insertUpload = "INSERT INTO uploads (original_name, file_name, file_path, file_type, file_size) 
                            VALUES ('$original_name', '$new_file_name', '$file_path', '$file_type', $file_size)";
            
            if ($conn->query($insertUpload) === TRUE) {
                $upload_id = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'message' => 'Dosya başarıyla yüklendi',
                    'data' => [
                        'upload_id' => $upload_id,
                        'file_name' => $new_file_name,
                        'file_path' => $file_path,
                        'original_name' => $original_name
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Veritabanına kayıt sırasında hata oluştu: ' . $conn->error
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Dosya yüklenirken hata oluştu'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Dosya bulunamadı'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek metodu'
    ]);
} 