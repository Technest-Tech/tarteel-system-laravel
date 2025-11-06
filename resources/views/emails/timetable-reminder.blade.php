<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تذكير حصة</title>
    <style>
        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #066ac9;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .content {
            padding: 20px 0;
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 150px;
        }
        .info-value {
            color: #666;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>تذكير: حصة قادمة خلال {{ $minutesBefore }} دقيقة</h2>
        </div>
        
        <div class="content">
            <div class="info-row">
                <span class="info-label">اسم الطالب:</span>
                <span class="info-value">{{ $studentName }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">اسم المعلم:</span>
                <span class="info-value">{{ $teacherName }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">اسم الحصة:</span>
                <span class="info-value">{{ $lessonName }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">تاريخ الحصة:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($lessonDate)->format('Y-m-d') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">وقت البداية:</span>
                <span class="info-value">{{ $startTime }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">وقت النهاية:</span>
                <span class="info-value">{{ $endTime }}</span>
            </div>
        </div>
        
        <div class="footer">
            <p>هذا تذكير تلقائي من نظام إدارة الحصص</p>
            <p>يرجى التأكد من الاستعداد للحصة في الوقت المحدد</p>
        </div>
    </div>
</body>
</html>

