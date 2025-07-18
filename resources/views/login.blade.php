<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Koncepto</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    .animate-fadeIn {
      animation: fadeIn 0.3s ease-out forwards;
    }

    @keyframes bounceScale {
      0%, 100% { transform: scale(1) translateY(0); opacity: 1; }
      50% { transform: scale(1.15) translateY(-10%); opacity: 0.8; }
    }

    .animate-bounceScale {
      animation: bounceScale 1.2s ease-in-out infinite;
    }

    #loadingOverlay svg {
      width: 48px;
      height: 48px;
    }

    #loadingOverlay span {
      font-size: 1.25rem; 
    }
  </style>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center">

  <div id="loadingOverlay" class="hidden fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-xl p-8 shadow-lg flex items-center space-x-5 animate-fadeIn">
      <svg class="animate-spin animate-bounceScale text-[#56AB2F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
      <span class="text-[#56AB2F] font-semibold">Logging in...</span>
    </div>
  </div>

  <div class="flex flex-col md:flex-row w-full h-screen">
    <div class="w-full md:w-[55%] bg-[#56AB2F] flex items-center justify-center relative">
      <img src="{{ asset('images/logo.png') }}" alt="Logo" class="max-w-[1000px] w-4/5" />
      <div class="hidden md:block absolute top-1/4 right-0.5 translate-x-2.5 -translate-y-2 bg-white opacity-50 text-black px-10 py-4 rounded-l-full text-2xl shadow font-bold">
        LOGIN
      </div>
    </div>

    <div class="w-full md:w-[45%] bg-white flex flex-col justify-center items-center p-8 md:p-16">
      <h2 class="text-4xl font-bold text-gray-800 mb-12">Welcome Back</h2>

      <form id="loginForm" method="POST" action="{{ route('login') }}" class="w-full max-w-md">
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
            class="w-full border-b-2 border-gray-300 focus:border-green-600 outline-none py-2 px-3 rounded-md text-gray-800" required />
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <div class="relative">
            <input type="password" name="password" id="password" placeholder="Enter your password"
              class="w-full border-b-2 border-gray-300 focus:border-green-600 outline-none py-2 px-3 rounded-md text-gray-800 pr-10" required />
            <button type="button" onclick="togglePassword()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S3.732 16.057 2.458 12z" />
              </svg>
            </button>
          </div>
        </div>

        <div class="mb-6 flex items-center">
          <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-green-600 rounded" checked />
          <label for="remember" class="ml-2 text-sm text-gray-700">Remember me</label>
        </div>

        <button type="submit"
          class="w-full bg-[#56AB2F] text-white font-bold py-2 rounded-lg hover:bg-green-700 transition shadow-md">
          LOGIN
        </button>
      </form>
    </div>
  </div>

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

    document.addEventListener("DOMContentLoaded", function () {
      const form = document.getElementById("loginForm");
      const overlay = document.getElementById("loadingOverlay");

      form.addEventListener("submit", function () {
        overlay.classList.remove("hidden");
      });
    });
  </script>
</body>
</html>
