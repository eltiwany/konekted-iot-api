<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" type="image/x-icon" href="{{ asset('images/konekted.png') }}">
        <title>Konekted IoT API - ● Running</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background: #dfefff !important;
                background: url('{{ URL::asset('img/hover.png') }}'), radial-gradient(#a3b7ff,#c6e3ff) !important;
                color: #636b6f;
                font-family: 'Arial', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
                color: #124177 !important;
            }

            h1 {
            }

            img {
                width: 150px;
                margin-top: -10px !important;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .large-dot {
                font-size: 20px;
                padding: 0px !important;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .btn-light {
                background: white;
                padding: 10px;
                border-radius: 5px;
            }

            .text-success {
                color: green;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <h1 class="m-b-md">
                    <img class="logo" src="{{ asset('images/konekted.png') }}" alt="Konekted Logo"/><br/>
                    <label>Konekted IoT API v{{ config('app.version') }}</label>
                </h1>
                <?php
                    $status = "<label class=\"text-success\"><label class='large-dot'>●</label> Running</label>";

                ?>
                <p class='btn-light'>
                    <b>Status: {!! $status !!}</b><br/>
                    <b>Uptime: <label id="uptime" class="text-success">@include('fetch-uptime')</label></b>
                </p>
            </div>
        </div>
        <script>
             function load() {

            }

             setInterval(function() {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    document.getElementById('uptime').innerHTML = this.responseText;
                };
                xhttp.open("GET", "/fetch-uptime", true);
                xhttp.send();
                //
             }, 60000);
            </script>
            <script src="{{ asset('js/jquery.min.js') }}"></script>
    </body>
</html>
