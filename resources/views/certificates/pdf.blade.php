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
            margin: 20px;
            background: url('{{ asset("images/emdr_europe_bg.png") }}') center center no-repeat;
            position: relative;
        }
        h1 {
            font-size: 46px;
        }
        p {
            font-size: 22px;
        }
        .logo {
            position: absolute;
            width: 200px;
        }
        .europe {
            top: 30px;
            left: 30px;
        }
        .spb {
            top: 10px;
            right: 20px;
        }
        .malik {
            position: absolute;
            bottom: 0px;
            right: 30px;
            text-align: center;
            width: 340px;
        }
        .malik p {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <img src="{{ asset('images/emdr_europe.png') }}" alt="EMDR Europe" class="logo europe"/>
        <img src="{{ asset('images/emdr_spb.png') }}" alt="EMDR Санкт-Петербург" class="logo spb"/>
        <h1>Сертификат участника</h1>
        <h2>{{ $participant->name ?? 'Марк Титов'}}</h2>
        <h3>{{ $certificate->webinar_name ?? 'Прокрастинация у шимпанзе' }}</h3>
        <p>{{ $certificate->hours ?? '' }}</p>
        <p><strong>Дата:</strong><br />{{ $certificate->date ?? '15 августа 2024' }}</p>
        <p><strong>Ведущий:</strong><br />{{ $certificate->lecturer_name ?? 'Наталья Помельникова'}}</p>
        <div class="malik">
            <img src="{{ asset('images/malik_sign.png') }}" alt="Юлия Малик"/>
            <h2>Юлия Малик</h2>
            <p>Президент Национальной Ассоциации EMDR России</p>
        </div>
    </div>
</body>
</html>