<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mapbox-key" content="{{ env('MAPBOX_KEY') }}">
    <meta name="default-country" content="{{ env('DEFAULT_COUNTRY') }}">

    <link rel="stylesheet" href="/css/app.css">

    <title>{{ env('APP_NAME') }}</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <script src="/js/vendor.js"></script>

</head>
<body class="skin-blue">

    <div id="app">
        <main-component></main-component>
    </div>
    <script src="/js/app.js"></script>
</body>
</html>
