@php
    use Carbon\Carbon;
    Carbon::setLocale('ru');
    $dateFormat = 'j F Y';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path("fonts/DejaVuLGCSans.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .certificate {
            border: 6px solid #6aa84f;
            border-radius: 100px;
            padding: 50px;
            margin: 0px;
            background: url('{{ public_path("storage/images/emdr_europe_bg.png") }}') center center no-repeat;
            position: relative;
            height: 580px;
        }
        h1.title {
            font-size: 44px;
        }
        h2.certificate-name {
            font-size: 32px;
            font-weight: bold;
            max-width: 500px;
            margin: 0 auto;
        }
        p {
            font-size: 22px;
            margin-bottom: 5px;
        }
        .name {
            font-size: 28px;
            font-weight: bold;
        }
        .logo {
            position: absolute;
        }
        .europe {
            top: 30px;
            left: 30px;
            width: 130px;
        }
        .spb {
            top: 10px;
            right: 20px;
            width: 150px;
        }
        .signature {
            position: absolute;
            bottom: 0px;
            text-align: center;
            width: 340px;
        }
        .signature.malik {
            right: 30px;
        }
        .signature.udi {
            left: 30px;
        }
        .signature h2 {
            font-size: 24px;
            line-height: 20px;
            margin: 0;
        }
        .signature p {
            font-size: 18px;
            line-height: 16px;
        }
        .signature img {
            width: 120px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <x-base64-image src="images/emdr_europe.png" alt="EMDR Europe" class="logo europe"/>
        <x-base64-image src="images/emdr_spb.png" alt="EMDR Санкт-Петербург" class="logo spb"/>
        
        
        <h1 class="title">Сертификат участника</h1>
        
        <h2 class="certificate-name">{{ $certificate->name }}</h2>

        <p class="name">{{ $participant->name }}</p>

        <div class="certificate-details">
            @if ($certificate->lecture_type === 'webinar')
                <p><strong>Часов:</strong> {{ $certificate->data['hours'] ?? 'N/A' }}</p>
                <p><strong>Ведущий:</strong> {{ $certificate->data['lecturer_name'] ?? 'N/A' }}</p>
                <p><strong>Дата:</strong><br />{{ isset($certificate->data['date']) ? Carbon::parse($certificate->data['date'])->format($dateFormat) : 'N/A' }}</p>
            @elseif ($certificate->lecture_type === 'event')
                <p>Дата начала: {{ isset($certificate->data['date']) ? Carbon::parse($certificate->data['date'])->format($dateFormat) : 'N/A' }}</p>
            @elseif ($certificate->lecture_type === 'module')
                <p>Номер сертификата: {{ $participant->data['certificate_number'] ?? 'N/A' }}</p>
                <p>Дата 1: {{ isset($participant->data['date_1']) ? Carbon::parse($participant->data['date_1'])->format($dateFormat) : 'N/A' }}</p>
                <p>Дата 2: {{ isset($participant->data['date_2']) ? Carbon::parse($participant->data['date_2'])->format($dateFormat) : 'N/A' }}</p>
            @elseif ($certificate->lecture_type === 'acknowledgment')
                <p>Текст: {{ $participant->data['text'] ?? 'N/A' }}</p>
                <p>Дата начала: {{ isset($participant->data['start_date']) ? Carbon::parse($participant->data['start_date'])->format($dateFormat) : 'N/A' }}</p>
                <p>Дата окончания: {{ isset($participant->data['end_date']) ? Carbon::parse($participant->data['end_date'])->format($dateFormat) : 'N/A' }}</p>
            @endif
        </div>
        
        @if ($certificate->lecture_type === 'module')
        <div class="signature udi">
            <x-base64-image src="images/malik_sign.png" alt="Юлия Малик"/>
            <h2>Уди Орен</h2>
            <p>Президент Национальной Ассоциации EMDR России</p>
        </div>
        @endif

        <div class="signature malik">
            <x-base64-image src="images/malik_sign.png" alt="Юлия Малик"/>
            <h2>Юлия Малик</h2>
            <p>Президент Национальной Ассоциации EMDR России</p>
        </div>
    </div>
</body>
</html>