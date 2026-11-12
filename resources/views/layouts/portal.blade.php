<!doctype html>
<html lang="{{ session('portal_lang', 'en') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SaaS' }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,-apple-system,Arial,sans-serif;background:#f5f7fb;color:#17233b}a{color:#3157d5;text-decoration:none}a:hover{text-decoration:underline}.nav{background:white;border-bottom:1px solid #e8ecf4;padding:17px max(20px,calc((100% - 1000px)/2));display:flex;align-items:center;justify-content:space-between;gap:20px}.brand{font-weight:800;color:#19284d;font-size:20px}.nav-right{display:flex;gap:15px;align-items:center}.lang{background:#f2f5ff;color:#2846aa;border:0;border-radius:9px;padding:8px 11px;cursor:pointer;font-weight:700}.container{width:min(100% - 36px,1000px);margin:44px auto}.card{background:#fff;border:1px solid #e8ecf3;border-radius:18px;box-shadow:0 14px 45px #2432570a;padding:30px}.auth{max-width:450px;margin:0 auto}.title{font-weight:800;letter-spacing:-.5px;font-size:27px;margin:0 0 8px}.sub{color:#778198;margin:0 0 25px;line-height:1.5}label{font-size:13px;font-weight:700;display:block;margin:17px 0 7px}input,select{width:100%;padding:12px 13px;border:1px solid #d9dfeb;border-radius:10px;font:inherit;color:#17233b;background:#fff}input:focus,select:focus{outline:2px solid #b5c4ff;border-color:#5873d8}.btn{background:#2e53d2;color:white;border:0;border-radius:10px;font-weight:700;padding:12px 17px;cursor:pointer;display:inline-block;text-align:center}.btn:hover{background:#2144bd;color:#fff;text-decoration:none}.btn-outline{background:#fff;color:#3456c1;border:1px solid #c5cde0}.btn-outline:hover{background:#f1f4fc;color:#1e3d9a}.wide{width:100%;margin-top:22px}.hint{font-size:13px;color:#7d879b;margin-top:17px}.alert{border-radius:11px;padding:12px 14px;margin:17px 0;font-size:14px}.error{color:#9e2432;background:#fff0f1;border:1px solid #ffd6dc}.success{color:#14683b;background:#e8fff1;border:1px solid #bbefd1}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}.shop{padding:20px;border:1px solid #e6ebf2;border-radius:14px;margin-top:16px}.shop h3{margin:0 0 8px}.small{font-size:13px;color:#778198}.inline{display:inline-block}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.footer{margin-top:50px;text-align:center;font-size:12px;color:#8c94a7}@media(max-width:600px){.nav{padding:16px 18px}.container{margin:24px auto}.card{padding:23px}.title{font-size:24px}}
    </style>
</head>
<body>
<header class="nav"><a class="brand" href="{{ url('/') }}">NexaBiz</a><div class="nav-right"><form action="{{ url('/language') }}" method="post">@csrf<input type="hidden" name="lang" value="{{ session('portal_lang', 'en') === 'en' ? 'bn' : 'en' }}"><button class="lang" type="submit">{{ session('portal_lang', 'en') === 'en' ? 'বাংলা' : 'EN' }}</button></form></div></header>
<main class="container">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
    @yield('content')
    <div class="footer">© {{ date('Y') }} NexaBiz</div>
</main>
</body></html>
