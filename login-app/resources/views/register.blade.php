<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Farmacéuticos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        
        <div class="login-panel">
            <div class="logo-section">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo">
                    <div class="logo-text">
                        <h2>Farmacos del Norte SAC</h2>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Registro de Usuario</h3>
                <p class="welcome-text">Por favor, completa el siguiente formulario para crear una cuenta.</p>

                @if($errors->any())
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" class="login-form">
                    @csrf
                    <div class="input-group">
                        <label for="usuario">Nombre de usuario</label>
                        <input type="text" 
                               id="usuario" 
                               name="usuario" 
                               value="{{ old('usuario') }}"
                               required 
                               autocomplete="username">
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autocomplete="new-password">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   autocomplete="new-password">
                            <button type="button" class="toggle-password" onclick="togglePasswordConfirmation()">
                                <i class="fas fa-eye" id="toggleIconConfirmation"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        REGISTRARSE
                    </button>
                </form>

                <div class="register-section">
                    <p class="register-text">¿Ya tienes una cuenta?</p>
                    <a href="{{ route('login') }}" class="btn-register">INICIAR SESIÓN</a>
                </div>
            </div>
        </div>

        <div class="image-panel">
            <div class="image-overlay">
                <img src="{{ asset('images/farmaceutico.jpg') }}" alt="Farmacéutico" class="hero-image">
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function togglePasswordConfirmation() {
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const toggleIconConfirmation = document.getElementById('toggleIconConfirmation');
            
            if (passwordConfirmationInput.type === 'password') {
                passwordConfirmationInput.type = 'text';
                toggleIconConfirmation.classList.remove('fa-eye');
                toggleIconConfirmation.classList.add('fa-eye-slash');
            } else {
                passwordConfirmationInput.type = 'password';
                toggleIconConfirmation.classList.remove('fa-eye-slash');
                toggleIconConfirmation.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>