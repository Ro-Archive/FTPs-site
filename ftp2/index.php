<?php
// Define your FTP credentials for the second FTP server
$server = 'server_name_here';
$ftp_username = 'ftp_username_here';
$ftp_password = 'ftp_password_here';

function listFtpContentsWithTimeAndSize($server, $ftp_username, $ftp_password, $directory) {
    $conn = ftp_connect($server);
    if (!$conn) {
        die('Could not connect to FTP server');
    }

    if (!ftp_login($conn, $ftp_username, $ftp_password)) {
        die('FTP login failed');
    }

    if (!ftp_chdir($conn, $directory)) {
        die('Failed to change directory');
    }

    $listing = ftp_rawlist($conn, ".");

    ftp_close($conn);

    $result = array();
    foreach ($listing as $item) {
        $parts = preg_split("/\s+/", $item);
        $modificationTime = date('Y-m-d H:i:s', strtotime($parts[5] . ' ' . $parts[6] . ' ' . $parts[7]));
        $itemName = end($parts);
        $isDirectory = $parts[0][0] === 'd';
        $size = $isDirectory ? '-' : $parts[4];

        if ($size !== '-') {
            $size = formatSize($size);
        }

        $result[] = array(
            'name' => $itemName,
            'modification_time' => $modificationTime,
            'size' => $size,
            'is_directory' => $isDirectory,
        );
    }

    return $result;
}

function formatSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) {
        $size /= 1024;
    }
    return round($size, 2) . ' ' . $units[$i];
}

if (isset($_GET['download'])) {
    $filename = $_GET['download'];

    $filename = basename($filename);

    $folder = '';
    $subfolder = '';

    if (isset($_GET['folder'])) {
        $folder = $_GET['folder'];
    }

    if (isset($_GET['subfolder'])) {
        $subfolder = $_GET['subfolder'];
    }

    $file_path = '/' . $folder . '/' . $subfolder . '/' . $filename;

    $conn = ftp_connect($server);
    if (!$conn) {
        die('Could not connect to FTP server');
    }

    if (!ftp_login($conn, $ftp_username, $ftp_password)) {
        die('FTP login failed');
    }

    $file_size = ftp_size($conn, $file_path);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $file_size);

    $temp_file = tmpfile();

    if (ftp_fget($conn, $temp_file, $file_path, FTP_BINARY, 0)) {
        fseek($temp_file, 0);

        while (!feof($temp_file)) {
            echo fread($temp_file, 8192);
            ob_flush();
            flush();
        }

        fclose($temp_file);
    } else {
        die('Error downloading the file from FTP');
    }

    ftp_close($conn);
    exit;
}

$folder = isset($_GET['folder']) ? $_GET['folder'] : '';
$subfolder = isset($_GET['subfolder']) ? $_GET['subfolder'] : '';

$directory = '/' . $folder . '/' . $subfolder;

$ftp_contents = listFtpContentsWithTimeAndSize($server, $ftp_username, $ftp_password, $directory);
?>

<!DOCTYPE html>
<html>
<head>
    <title>wdig-fc-disneyextreme</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #333;
            color: #fff;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #fff;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #007bff;
        }

        tr:nth-child(even) {
            background-color: #444;
        }

        .download-link, .play-link, .view-link {
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
        }

        .about {
            margin: 20px auto;
            width: 80%;
            padding: 10px;
            background-color: #444;
            border: 1px solid #fff;
        }
    </style>
</head>
<body>
    <div class="about">
        <h2>About This Page</h2>
        <p>This page lists files and subfolders in the FTP directory with modification times and sizes. You can view, download, or play MP4 videos or view PNG, JPEG, JPG, and GIF files by clicking the respective links.</p>
        <p>Please note that larger files may take longer to load or download, especially on slower internet connections.</p>
    </div>

    <table>
        <tr>
            <th>Name</th>
            <th>Modification Time</th>
            <th>Size</th>
            <th>Action</th>
        </tr>
        <?php foreach ($ftp_contents as $item) : ?>
            <tr>
                <?php
                $itemName = $item['name'];
                $modificationTime = $item['modification_time'];
                $size = $item['size'];
                $isDirectory = $item['is_directory'];

                echo '<td>' . ($isDirectory ? '<b>' . $itemName . '</b>' : $itemName) . '</td>';
                echo '<td>' . $modificationTime . '</td>';
                echo '<td>' . $size . '</td>';

                if ($isDirectory) {
                    $viewUrl = '?folder=' . urlencode($folder) . '&subfolder=' . urlencode($subfolder . '/' . $itemName);
                    echo '<td><a class="view-link" href="' . $viewUrl . '">View</a></td>';
                } else {
                    $downloadUrl = '?folder=' . urlencode($folder) . '&subfolder=' . urlencode($subfolder) . '&download=' . urlencode($itemName);
                    echo '<td><a class="download-link" href="' . $downloadUrl . '">Download</a>';

                    // Check if it's one of the supported file extensions (MP4, PNG, JPEG, JPG, GIF)
                    $fileExtension = strtolower(pathinfo($itemName, PATHINFO_EXTENSION));
                    if (in_array($fileExtension, ['mp4', 'png', 'jpeg', 'jpg', 'gif'])) {
                        if ($fileExtension === 'mp4') {
                            $playUrl = 'Player/?file_name=' . urlencode($itemName) . '&folder=' . urlencode($folder) . '&subfolder=' . urlencode($subfolder);
                            echo ' | <a class="play-link" href="' . $playUrl . '" target="_blank">Play</a>';
                        } else {
                            $viewUrl = 'Player/?file_name=' . urlencode($itemName) . '&folder=' . urlencode($folder) . '&subfolder=' . urlencode($subfolder);
                            echo ' | <a class="view-link" href="' . $viewUrl . '" target="_blank">View</a>';
                        }
                    }

                    echo '</td>';
                }
                ?>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
