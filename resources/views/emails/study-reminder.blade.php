<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weekly Study Reminder</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f6f8; font-family: 'Segoe UI', Arial, sans-serif; color: #333; }
    .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #3b4fd8 0%, #6c5ce7 100%); padding: 32px 36px; text-align: center; }
    .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
    .header p  { margin: 8px 0 0; color: rgba(255,255,255,.85); font-size: 14px; }
    .body { padding: 32px 36px; }
    .greeting { font-size: 16px; margin-bottom: 20px; }
    .course-card { border: 1px solid #e8eaf0; border-radius: 10px; padding: 16px 20px; margin-bottom: 14px; display: flex; align-items: center; gap: 16px; }
    .course-icon { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #3b4fd8, #6c5ce7); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .course-icon span { color: #fff; font-size: 20px; }
    .course-info { flex: 1; }
    .course-title { font-weight: 600; font-size: 15px; margin: 0 0 4px; }
    .course-meta { font-size: 12px; color: #888; margin: 0; }
    .progress-bar { height: 6px; background: #eee; border-radius: 4px; margin-top: 8px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #3b4fd8, #6c5ce7); border-radius: 4px; }
    .cta { text-align: center; margin: 28px 0 8px; }
    .cta a { background: linear-gradient(135deg, #3b4fd8, #6c5ce7); color: #fff; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-weight: 600; font-size: 15px; display: inline-block; }
    .footer { background: #f8f9fc; padding: 20px 36px; text-align: center; font-size: 12px; color: #aaa; }
    .footer a { color: #3b4fd8; text-decoration: none; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>📚 Your Weekly Study Reminder</h1>
      <p>Keep the momentum going, {{ $employeeName }}!</p>
    </div>
    <div class="body">
      <p class="greeting">Hi <strong>{{ $employeeName }}</strong>,</p>
      <p>This is your weekly reminder to continue your learning journey. Here's where you left off:</p>

      @foreach ($courses as $course)
      <div class="course-card">
        <div class="course-icon"><span>📖</span></div>
        <div class="course-info">
          <p class="course-title">{{ $course['title'] }}</p>
          <p class="course-meta">{{ $course['category'] }} · {{ $course['progress'] }}% complete</p>
          <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $course['progress'] }}%"></div>
          </div>
        </div>
      </div>
      @endforeach

      <div class="cta">
        <a href="{{ config('app.url') }}/staff-development">Continue Learning →</a>
      </div>

      <p style="font-size:13px;color:#888;text-align:center;margin-top:20px;">
        You're receiving this because you enabled weekly study reminders.<br>
        You can turn them off anytime in the <a href="{{ config('app.url') }}/staff-development" style="color:#3b4fd8">Staff Development</a> section.
      </p>
    </div>
    <div class="footer">
      © {{ date('Y') }} Boxleo Courier · <a href="{{ config('app.url') }}">hrm.boxleocourier.com</a>
    </div>
  </div>
</body>
</html>
