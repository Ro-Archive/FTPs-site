<!DOCTYPE html>
<html>
<head>
    <title>FTP Selector</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #333;
            color: #fff;
        }

        h1 {
            text-align: center;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .ftp-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 20px;
            transition: background-color 0.3s ease, transform 0.3s ease; 
        }

        .ftp-btn:hover {
            background-color: #0056b3; 
            transform: scale(1.1); 
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2); 
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        h1:hover {
            animation: rotate 2s linear infinite;
        }

        .info {
            text-align: center;
            margin-top: 20px;
        }

        .info p {
            font-size: 18px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    
        <div class="info">
        <p>Welcome to the FTP Selector.</p>
        <p>Click on the buttons below to access different FTP locations.</p>
    </div>

    <div class="btn-container">
        <a class="ftp-btn" href="ftp">ftp</a>
        <a class="ftp-btn" href="ftp2">ftp2</a>
    </div>
</body>
</html>
