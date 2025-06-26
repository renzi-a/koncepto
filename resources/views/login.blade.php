<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Koncepto</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {x
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<script>
  function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (input.type === "password") {
      input.type = "text";
      icon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.955 9.955 0 012.207-3.362m2.501-1.957A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.953 9.953 0 01-4.058 4.62M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" />`;
    } else {
      input.type = "password";
      icon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S3.732 16.057 2.458 12z" />`;
    }
  }
</script>


<body class="min-h-screen bg-gray-100 flex items-center justify-center">
  <div class="flex flex-col md:flex-row w-full h-screen">
    
    <div class="w-full md:w-[55%] bg-[#56AB2F] flex items-center justify-center relative">
      <img src="{{ asset('images/logo.png') }}" alt="Logo" alt="Koncepto" class="max-w-[1000px] w-4/5">
      <div class="hidden md:block absolute top-1/4 right-0.5 translate-x-2.5 -translate-y-2 bg-white opacity-50 text-black px-10 py-4 rounded-l-full text-2xl shadow font-bold">
        LOGIN
        </div>


    </div>

    <div class="w-full md:w-[45%] bg-white flex flex-col justify-center items-center p-8 md:p-16">
        <h2 class="text-4xl font-bold text-gray-800 mb-12">Welcome Back</h2>
        
        <form method="POST" action="{{ route('login') }}" class="w-full max-w-md">
        @csrf
        
        @if ($errors->any())
        <div class="mb-4 text-red-600 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif
        
        <div class="mb-6">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input type="email" name="email" id="email" placeholder="Enter your email"
            class="w-full border-b-2 border-gray-300 focus:border-green-600 outline-none py-2 px-3 rounded-md text-gray-800" required>
        </div>
       <div class="mb-6">
      <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
      <div class="relative">
        <input type="password" name="password" id="password" placeholder="Enter your password"
          class="w-full border-b-2 border-gray-300 focus:border-green-600 outline-none py-2 px-3 rounded-md text-gray-800 pr-10" required>
        
        <!-- Eye Icon Button -->
        <button type="button" onclick="togglePassword()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
          <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S3.732 16.057 2.458 12z" />
          </svg>
        </button>
      </div>
    </div>

    <div class="mb-6 flex items-center">
      <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-green-600 rounded" checked>
      <label for="remember" class="ml-2 text-sm text-gray-700">Remember me</label>
    </div>

        <button type="submit"
          class="w-full bg-[#56AB2F] text-white font-bold py-2 rounded-lg hover:bg-green-700 transition shadow-md">
          LOGIN
        </button<?php
    if (auth()->check()) {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 01 Jan 1990 00:00:00 GMT");
    }
?>
>
      </form>
    </div>
  </div>
</body>
</html>
