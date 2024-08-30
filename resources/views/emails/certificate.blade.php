<!DOCTYPE html>
<html>
<head>
    <title>Ваш сертификат</title>
</head>
<body>
    <p>Уважаемая(ый) {{ $participant->name }},</p>
    <p>Вы успешно прошли курс {{ $participant->certificate->webinar_name }}. Ваш сертификат прикреплен к письму.</p>
    <p>С уважением,<br>Санкт-Петербургский Центр EMDR/ДПДГ</p>
</body>
</html>