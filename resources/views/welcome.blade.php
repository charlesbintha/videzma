<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Videzma - Service de vidange a domicile</title>
    <link rel="icon" href="{{ asset('assets/images/logo/videzma-icon.png') }}" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #E8F5E9 0%, #FFF3E0 50%, #FFEBEE 100%);
            color: #1f2937;
            min-height: 100vh;
        }

        .wrap {
            max-width: 720px;
            margin: 0 auto;
            padding: 60px 20px;
            text-align: center;
        }

        .logo {
            margin-bottom: 32px;
        }

        .logo img {
            height: 140px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #F15A22;
            margin-bottom: 16px;
            font-weight: 700;
        }

        p {
            color: #5b6b77;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4CB050, #F15A22);
            color: #fff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(241, 90, 34, 0.3);
        }

        .features {
            display: flex;
            gap: 20px;
            margin-top: 32px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .feature {
            flex: 1;
            min-width: 150px;
            padding: 16px;
            background: #f8f9fb;
            border-radius: 12px;
        }

        .feature-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .feature h3 {
            color: #4CB050;
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="logo">
            <img src="{{ asset('assets/images/logo/videzma.png') }}" alt="Videzma">
        </div>
        <div class="card">
            <h1>Bienvenue sur Videzma</h1>
            <p>Plateforme de gestion des services de vidange a domicile. Connectez clients et vidangeurs professionnels en toute simplicite.</p>
            <a href="{{ route('login') }}" class="btn">Acceder a l'administration</a>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">🚚</div>
                    <h3>Vidangeurs</h3>
                </div>
                <div class="feature">
                    <div class="feature-icon">📍</div>
                    <h3>Geolocalisation</h3>
                </div>
                <div class="feature">
                    <div class="feature-icon">📱</div>
                    <h3>Application mobile</h3>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
