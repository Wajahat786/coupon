<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= (int) ($code ?? 404) ?> · DealHub</title>
<link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="centered-page">
<div class="auth-card center">
  <h1 style="font-size:3rem"><?= (int) ($code ?? 404) ?></h1>
  <p class="muted"><?= e($title ?? 'Something went wrong') ?></p>
  <a class="btn btn-primary" href="/">Back to home</a>
</div>
</body>
</html>
