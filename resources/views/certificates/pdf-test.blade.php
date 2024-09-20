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
        .module-dates {
            width: 500px;
            margin: 0 auto;
        }
        .module-dates p {
            display: inline-block;
            width: 50%;
            margin: 0;
            padding: 0;
        }
        .module-dates p {
            text-align: center;
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
            bottom: 10px;
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
        .malik img {
            width: 120px;
        }
        .udi img {
            width: 190px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        {{-- <x-base64-image src="images/emdr_europe.png" alt="EMDR Europe" class="logo europe"/> --}}
        <img src="{{asset('images/emdr_europe.png')}}" alt="EMDR Europe" class="logo europe"/>
        <x-base64-image src="images/emdr_spb.png" alt="EMDR Санкт-Петербург" class="logo spb"/>
        
        
            <h1 class="title">Сертификат участника</h1>
        
            <h2 class="certificate-name">{{ $certificate->name ?? 'Трансрегуляция биомеханических псевдоматерий' }}</h2>

        <p class="name">{{ $participant->name ?? 'Марк Титов'}}</p>

        <div class="certificate-details">
                <p><strong>Часов:</strong> {{ $certificate->data['hours'] ?? '5' }}</p>
                <p><strong>Ведущий:</strong> {{ $certificate->data['lecturer_name'] ?? 'Юлия Глазкова' }}</p>
                <p><strong>Дата:</strong><br />{{ isset($certificate->data['date']) ? Carbon::parse($certificate->data['date'])->format($dateFormat) : '8 ноября 2024' }}</p>
            
        </div>

        <div class="signature malik">
            <x-base64-image src="images/malik_sign.png" alt="Юлия Малик"/>
            <h2>Юлия Малик</h2>
            <p>Президент Национальной Ассоциации EMDR России</p>
        </div>
    </div>
</body>
</html>