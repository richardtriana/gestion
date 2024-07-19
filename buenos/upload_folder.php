<?php
session_start();
$current_dir = urldecode($_GET['dir']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Carpeta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        header {
            background: #2a3d66;
            color: white;
            padding: 10px;
            text-align: center;
            width: 100%;
            position: fixed;
            top: 0;
        }
        .info-container {
            margin-top: 70px;
            text-align: center;
        }
        .upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .upload-form input[type="file"] {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 5px;
        }
        .upload-form button {
            background: #4ca1af;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .upload-form button:hover {
            opacity: 0.8;
        }
        .upload-form a {
            text-decoration: none;
            color: #4ca1af;
            margin-top: 10px;
        }
        .upload-form a:hover {
            text-decoration: underline;
        }
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            display: none;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
        }
        .modal-content h2 {
            margin-top: 0;
        }
        .modal-content button {
            background: #4ca1af;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .modal-content button:hover {
            opacity: 0.8;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Subir Carpeta</h1>
    </header>
    <div class="info-container">
        <span>Sube tu carpeta al directorio: <?php echo htmlspecialchars($current_dir); ?></span>
    </div>
    <main>
        <div class="content-panel">
            <form id="uploadForm" action="upload_folder_process.php?dir=<?php echo urlencode($current_dir); ?>" method="post" enctype="multipart/form-data" class="upload-form">
                <input type="file" name="folder[]" webkitdirectory directory multiple required>
                <input type="hidden" name="folder_name" id="folderName">
                <button type="submit">Subir Carpeta</button>
                <a href="gestion.php?dir=<?php echo urlencode($current_dir); ?>">Volver</a>
            </form>
        </div>
    </main>
    <div id="progressModal" class="modal">
        <div class="modal-content">
            <span class="close" id="progressCloseBtn">&times;</span>
            <div class="loader" id="loader"></div>
            <h2 id="progressText">Subiendo carpeta...</h2>
        </div>
    </div>
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(event) {
            const files = document.querySelector('input[type="file"]').files;
            if (files.length > 0) {
                const fullPath = files[0].webkitRelativePath;
                const folderName = fullPath.split('/')[0];
                document.getElementById('folderName').value = folderName;
            }
            document.getElementById('progressModal').style.display = 'block';
            document.getElementById('loader').style.display = 'block';
            document.getElementById('progressText').innerText = 'Subiendo carpeta...';
        });

        document.getElementById('progressCloseBtn').onclick = function() {
            document.getElementById('progressModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('progressModal')) {
                document.getElementById('progressModal').style.display = 'none';
            }
        }
    </script>
</body>
</html>



























