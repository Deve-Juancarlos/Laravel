<!-- resources/views/auth/welcome-message.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .blur-background {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .fade-slide {
            animation: fadeSlideUp 0.7s ease-out;
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.92); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .overlay {
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gray-100">

    <!-- FONDO: LOGIN BORROSO -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="blur-background h-full w-full scale-105">
            @include('login') <!-- TU LOGIN -->
        </div>
    </div>

    <!-- CAPA OSCURA -->
    <div class="fixed inset-0 overlay z-10"></div>

    <!-- CARD DE BIENVENIDA -->
    <div class="relative z-20 fade-slide glass-card p-10 rounded-3xl text-center max-w-md w-full mx-6 shadow-2xl">
        <div class="mb-6">
            <svg class="w-20 h-20 mx-auto text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-white mb-2">
            ¡Bienvenido, <span class="text-emerald-300">{{ session('user_name') }}</span>!
        </h1>
        <p class="text-gray-200 mb-8">Tu cuenta ha sido creada con éxito.</p>

        <div class="flex items-center justify-center text-sm text-gray-300">
            <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent mr-2"></div>
            <span>Redirigiendo al login...</span>
        </div>
    </div>

    <!-- REDIRECCIÓN -->
    <script>
        setTimeout(() => {
            const card = document.querySelector('.glass-card');
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateY(-30px) scale(0.9)';
            setTimeout(() => {
                window.location.href = "{{ route('login') }}";
            }, 500);
        }, 3000);
    </script>

</body>
</html>