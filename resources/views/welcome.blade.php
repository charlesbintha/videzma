<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wadnou</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f8f9fb;
            color: #1f2937;
        }

        .wrap {
            max-width: 720px;
            margin: 80px auto;
            padding: 0 20px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
        }

        a {
            color: #1d4ed8;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Bienvenue sur Wadnou</h1>
            <p>Cette page est un point d'entree temporaire. Accedez a l'administration pour continuer.</p>
            <p><a href="{{ route('login') }}">Connexion admin</a></p>
        </div>
    </div>
</body>
</html>
