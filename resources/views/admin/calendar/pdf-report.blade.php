<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير التقويم</title>
    <style>
        @charset "UTF-8";
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #066ac9;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #066ac9;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #066ac9;
            color: white;
        }
        th, td {
            padding: 10px;
            text-align: right;
            border: 1px solid #ddd;
        }
        th {
            font-weight: bold;
            font-size: 13px;
        }
        td {
            font-size: 12px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f5f5f5;
        }
        .no-events {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>أكاديمية ترتيل</h1>
        <p>تقرير تقويم الحصص</p>
    </div>

    <div class="date-range">
        <strong>من:</strong> {{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }} 
        <strong>إلى:</strong> {{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}
    </div>

    @if(count($events) > 0)
        <table>
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>وقت البداية</th>
                    <th>وقت النهاية</th>
                    <th>الطالب</th>
                    <th>المعلم</th>
                    <th>اسم الحصة</th>
                    <th>النوع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($event['date'])->format('Y-m-d') }}</td>
                        <td>{{ $event['start_time'] }}</td>
                        <td>{{ $event['end_time'] ?? '-' }}</td>
                        <td>{{ $event['student_name'] }}</td>
                        <td>{{ $event['teacher_name'] }}</td>
                        <td>{{ $event['lesson_name'] ?? '-' }}</td>
                        <td>{{ $event['type'] == 'timetable' ? 'جدول' : 'حصة' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-events">
            لا توجد أحداث في هذا النطاق الزمني
        </div>
    @endif

    <div class="footer">
        <p>تم إنشاء هذا التقرير في {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>

