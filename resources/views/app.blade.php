@php($theme = config('settings.app.theme.active', 'default'));
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }}</title>
  @vite("resources/js/Themes/{$theme}/app.js")
  @inertiaHead
</head>
<body>
  @inertia
</body>
</html>