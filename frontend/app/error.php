<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Error Interno del Servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #1D222B;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            text-align: center;
            background: #212631;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        .error-container h1 {
            font-size: 120px;
            color: #FF6F61;
            font-weight: 700;
        }
        .error-container h2 {
            font-size: 1.8rem;
            color: #6c757d;
        }
        .error-container p {
            color: #adb5bd;
            font-size: 1.1rem;
            margin-top: 20px;
        }
        .btn-back {
            margin-top: 30px;
            padding: 12px 24px;
            font-size: 1rem;
            background-color: #FF6F61;
            color: white;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .btn-back:hover {
            background-color: #e65f4e;
            text-decoration: none;
        }
        .logo-container img {
            width: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo-container">
            <!-- Puedes poner tu logo aquí si lo deseas -->
            <img src="./images/logoAGArte.svg" alt="Logo">
        </div>
        <h1>500</h1>
        <h2>Error Interno del Servidor</h2>
        <p>Lo sentimos, algo salió mal en nuestro servidor. Estamos trabajando para solucionarlo. Por favor, inténtalo más tarde.</p>
        <a href="/home.php" class="btn btn-back">Volver al Inicio</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
