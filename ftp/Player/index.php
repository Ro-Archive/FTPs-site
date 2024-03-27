<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['file_name'])) {
        $fileName = $_GET['file_name'];
        $folder = isset($_GET['folder']) ? $_GET['folder'] : '';
        $subfolder = isset($_GET['subfolder']) ? $_GET['subfolder'] : '';

        // Define your FTP credentials for the second FTP server
        $server = 'server_name_here';
        $ftp_username = 'ftp_username_here';
        $ftp_password = 'ftp_password_here';

        $fileToPlay = '/' . $folder . '/' . $subfolder . '/' . $fileName;

        $conn = ftp_connect($server);
        if (!$conn) {
            die('Could not connect to FTP server');
        }

        if (!ftp_login($conn, $ftp_username, $ftp_password)) {
            die('FTP login failed');
        }

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, ['gif', 'jpeg', 'jpg', 'png'])) {
            $contentType = 'image/' . $fileExtension;
            header('Content-Type: ' . $contentType);

            // Display the image directly
            if (ftp_fget($conn, fopen('php://output', 'wb'), $fileToPlay, FTP_BINARY, 0)) {
                ftp_close($conn);
                exit;
            } else {
                die('Failed to display the image');
            }
        } elseif ($fileExtension === 'mp4') {
            header('Content-Type: video/mp4');

            // Stream the video file directly to the media player
            if (ftp_fget($conn, fopen('php://output', 'wb'), $fileToPlay, FTP_BINARY, 0)) {
                ftp_close($conn);
                exit;
            } else {
                die('Failed to stream the video file');
            }
        } else {
            die('Unsupported file format');
        }
    } else {
        die('File name not provided');
    }
} else {
    die('Invalid request method');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Media Viewer</title>
</head>
<body>
    <?php
    if (isset($contentType) && strpos($contentType, 'image/') === 0) {
        echo '<img src="?file_name=' . urlencode($fileName) . '" alt="Image">';
    }
    ?>

    <video id="video-player" width="420" height="420" controls autoplay>
        <source id="video-source" src="" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <script>
        var videoSource = document.getElementById('video-source');

        var queryString = window.location.search;
        var urlParams = new URLSearchParams(queryString);
        var fileName = urlParams.get('file_name');

        // Set the src attribute to the downloaded file name
        videoSource.setAttribute('src', '?file_name=' + fileName);
    </script>
</body>
</html>
