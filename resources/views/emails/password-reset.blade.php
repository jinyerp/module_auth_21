<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 재설정 안내</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a5568;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">비밀번호 재설정</h1>
    </div>
    
    <div class="content">
        <p>안녕하세요, {{ $user->name }}님</p>
        
        <p>비밀번호 재설정을 요청하셨습니다. 아래 버튼을 클릭하여 새로운 비밀번호를 설정하실 수 있습니다.</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetLink }}" class="button">비밀번호 재설정하기</a>
        </div>
        
        <p>또는 아래 링크를 브라우저에 직접 입력하셔도 됩니다:</p>
        <p style="word-wrap: break-word; color: #4f46e5;">{{ $resetLink }}</p>
        
        <div class="footer">
            <p><strong>주의사항:</strong></p>
            <ul>
                <li>이 링크는 1시간 동안 유효합니다.</li>
                <li>비밀번호 재설정을 요청하지 않으셨다면 이 이메일을 무시하셔도 됩니다.</li>
                <li>문의사항이 있으시면 고객센터로 연락주시기 바랍니다.</li>
            </ul>
            
            <p style="text-align: center; color: #a0aec0; margin-top: 20px;">
                © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>