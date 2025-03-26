<?php
http_response_code(404); // Définit le code de réponse HTTP à 404
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #d96666; /* Fond rouge */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            font-size: 50px;
            color: #000;
            margin-bottom: 10px;
        }
        p {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            color: white;
            background-color: black;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        a:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Erreur : 404</h1>
        <p>Oups ! La page que vous recherchez n'existe pas.</p>
        <a href="index.html">Retour à l'accueil</a>
    </div>
</body>
</html>
