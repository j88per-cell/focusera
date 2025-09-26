@php(
  $theme = config('site.theme.active')
    ?? config('settings.site.theme.active')
    ?? 'Default'
)
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
