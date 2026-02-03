<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&display=swap');
        body { font-family: 'Montserrat', sans-serif; color: #1c2c5b; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f7f9; }
        .container { width: 100%; max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 8px solid #00529f; }
        .header { padding: 30px; text-align: left; position: relative; }
        .logo { position: absolute; top: 20px; right: 20px; width: 80px; }
        .content { padding: 40px; }
        h1 { color: #00529f; font-weight: 800; text-transform: uppercase; margin-top: 0; font-size: 24px; }
        .details-box { background-color: #00529f; color: #ffffff; padding: 25px; border-radius: 12px; margin: 25px 0; }
        .label { font-weight: 400; opacity: 0.8; font-size: 12px; text-transform: uppercase; display: block; }
        .value { font-weight: 700; font-size: 18px; }
        .footer { background-color: #f8f9fa; color: #a0a0a0; padding: 20px; text-align: center; font-size: 11px; border-top: 1px solid #eee; }
        .highlight { color: #00529f; font-weight: 700; }
        .bold-text { font-weight: 800; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://upload.wikimedia.org/wikipedia/en/thumb/5/56/Real_Madrid_CF.svg/1200px-Real_Madrid_CF.svg.png" class="logo">
            <h1>Sikeres<br><span class="bold-text">Jegyvásárlás</span></h1>
        </div>
        <div class="content">
            <p>Kedves <span class="highlight">{{ $ticket->user->name }}</span>!</p>
            <p class="bold-text">Hala Madrid! A foglalásod megerősítésre került.</p>
            <div class="details-box">
                <span class="label">Mérkőzés</span>
                <span class="value">{{ $ticket->game->team_home->team_name }} vs {{ $ticket->game->team_away->team_name }}</span>
            </div>
            <p>A belépéshez szükséges jegyet <span class="bold-text">PDF formátumban csatoltuk</span> ehhez az üzenethez.</p>
        </div>
        <div class="footer"><p>&copy; 2026 Real Madrid C.F.</p></div>
    </div>
</body>
</html>