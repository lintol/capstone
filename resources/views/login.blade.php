<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
        function ckanTarget(server) {
            var target = "{{ URL::route('login.by-driver', ['driver' => 'ckan']) }}?server=";
            window.location = target + server;
        }
        
        function ckanSwap() {
            var btn = document.getElementById('ckanSwapBtn');
            var form = document.getElementById('ckanServerDiv');
        
            btn.style.display = 'none';
            form.style.display = 'block';
        }
        </script>
    
         <title>Lintol</title>
  
          <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Montserrat" />

        <!-- Styles -->
        <style>
            html, body {
                background-color: #F8F8F8;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
                font-family: Montserrat;
            }
            .logo {
               height: 38px;
               width: 132px;
               margin: 0px auto 23px;
               color: red;
               display: block;
            }
            .instructions {
              font-size: 14px;
              color: #000000;
              margin-bottom: 30px;
              text-align: center; 
            }
            .loginBox {
               border: 1px solid #D4D4D4;
               background-color: #FFFFFF;
               width: 508px;
               height: 350px;
               margin: 181px  auto 0px;
            }
            .oAuthButton {
               border 1px solid black;
               border-radius: 3px;
               color: white;
               width: 248px;
               height: 40px;
               display: block;
               margin: 10px auto;
            }
            .github {
               background-color: #000000;
            }
            .ckan {
               background-color: #f44b42;
            }
            .box {
              margin: 28px auto 0px;
              display: block;
              width: 350px;
            }
            .ckanServerDetails {
               margin: 0px auto;
               width: 250px;
            }
            .ckanLogin {
              margin: auto;
              display: block;
              margin-top: 5px;
              color: white;
              padding: 5px;
            }
            .terms {
              text-align: center
            }
        </style>
    </head>
    <body>
      <div class="loginBox">
        <div class="box">
        {{ HTML::image('images/logo.svg', 'Logo for Project Lintol', array('class' => 'logo')) }}
        <p class="instructions" >Please Login with the following services</p>
        <div>
          @if (config('capstone.features.services-github', false))
          <button id="githubBtn" class="oAuthButton github" onClick='window.location="{{ URL::route('login.by-driver', ['driver' => 'github']) }}"'>Sign in with Github</button>
          @endif
          <button id='ckanSwapBtn' class="oAuthButton ckan" onClick='ckanSwap()'>Sign in with CKAN</button>
          <div id='ckanServerDiv'  style="display: none" class="ckanServerDetails">
             <button class="oAuthButton ckan" onClick='ckanTarget(document.getElementById("ckanServer").value)'>Sign in with CKAN</button>
             <label class="instructions" for="ckanServer" >Address:</label>
             <select id='ckanServer' style="display: inline"  name='ckanServer'>
                <option disabled>(select a valid server)</option>
             @foreach (config('capstone.authentication.ckan.valid-servers', []) as $server)
                <option value='{{ $server }}'>{{ $server }}</option>
             @endforeach
             </select>
          </div>
          <div class="terms">
              <a href='{{ config('capstone.documents.terms-and-conditions') }}'>Terms &amp; Conditions</a> -
              <a href='{{ config('capstone.documents.privacy-notice') }}'>Privacy Notice</a>
          </div>
        </div>
      </div>
      </div>
    </body>
</html>
