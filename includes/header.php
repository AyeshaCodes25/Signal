<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Signal' : 'Signal — Task Control Log' ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><text y=%220.9em%22 font-size=%2220%22>📡</text></svg>">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          hull: '#0D2B2E',
          panel: '#123638',
          panel2: '#173F41',
          ink: '#0A2224',
          signal: '#E3A857',
          flare: '#D65A4A',
          sage: '#5FA88C',
          ivory: '#F1EEE4',
          mist: '#8FA9A6',
        },
        fontFamily: {
          display: ['Space Grotesk', 'sans-serif'],
          body: ['Inter', 'sans-serif'],
          mono: ['IBM Plex Mono', 'monospace'],
        },
      },
    },
  };
</script>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-hull text-ivory font-body min-h-screen">
