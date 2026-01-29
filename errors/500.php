<?php

/**
 * Babybib - 500 Error Page
 */
$pageTitle = 'เกิดข้อผิดพลาด';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - <?php echo $pageTitle; ?> | Babybib</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e1e2d 0%, #3b1a1a 50%, #1e1e2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            position: relative;
            z-index: 1;
        }

        .error-code {
            font-size: clamp(100px, 20vw, 180px);
            font-weight: 800;
            background: linear-gradient(135deg, #EF4444 0%, #F97316 50%, #FBBF24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 10px;
            animation: shake 0.5s ease-in-out infinite;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-2px);
            }

            75% {
                transform: translateX(2px);
            }
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .error-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.95);
        }

        .error-message {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* Floating shapes */
        .shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: floatShape 20s infinite;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            background: #EF4444;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            background: #F97316;
            bottom: -50px;
            left: -50px;
            animation-delay: 5s;
        }

        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            background: #FBBF24;
            top: 50%;
            left: 10%;
            animation-delay: 10s;
        }

        @keyframes floatShape {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(30px, 30px) rotate(90deg);
            }

            50% {
                transform: translate(0, 50px) rotate(180deg);
            }

            75% {
                transform: translate(-30px, 30px) rotate(270deg);
            }
        }

        .brand {
            position: fixed;
            top: 30px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 20px;
            z-index: 10;
        }

        .brand i {
            color: #EF4444;
        }

        .error-details {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: left;
        }

        .error-details summary {
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        .error-details p {
            margin-top: 15px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <a href="/babybib_db/" class="brand">
        <i class="fas fa-book-open"></i>
        Babybib
    </a>

    <div class="error-container">
        <div class="error-icon">⚠️🔧</div>
        <div class="error-code">500</div>
        <h1 class="error-title">เกิดข้อผิดพลาดภายในระบบ</h1>
        <p class="error-message">
            ขออภัย เซิร์ฟเวอร์ไม่สามารถดำเนินการตามคำขอได้<br>
            กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ
        </p>
        <div class="btn-group">
            <a href="/babybib_db/" class="btn btn-primary">
                <i class="fas fa-home"></i>
                กลับหน้าหลัก
            </a>
            <a href="javascript:location.reload()" class="btn btn-secondary">
                <i class="fas fa-redo"></i>
                ลองใหม่อีกครั้ง
            </a>
        </div>

        <details class="error-details">
            <summary>ข้อมูลเพิ่มเติมสำหรับผู้ดูแลระบบ</summary>
            <p>
                Time: <?php echo date('Y-m-d H:i:s'); ?><br>
                Request: <?php echo $_SERVER['REQUEST_URI'] ?? 'N/A'; ?><br>
                IP: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?>
            </p>
        </details>
    </div>
</body>

</html>