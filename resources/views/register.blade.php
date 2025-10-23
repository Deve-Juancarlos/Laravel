<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Farmacéuticos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
</head>
<body>
    <div class="register-container">
       
        <div class="register-panel">
            <div class="logo-section">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo de Farmaco del Norte SAC">
                    <div class="logo-text">
                        <h2>Farmaco del Norte SAC</h2>                        
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Registro de Usuario</h3>
                <p class="welcome-text">Completa el formulario para crear tu cuenta y acceder al sistema SEDIM .</p>

                @if($errors->any())
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" class="register-form">
                    @csrf
                    
                    <div class="input-group">
                        <label for="usuario">Usuario</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   id="usuario" 
                                   name="usuario" 
                                   value="{{ old('usuario') }}"
                                   required 
                                   autocomplete="username"
                                   placeholder="Ingresa tu nombre de usuario">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="tipousuario">Tipo de Usuario</label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-badge input-icon"></i>
                            <select id="tipousuario" name="tipousuario" required>
                                <option value="">Seleccionar tipo de usuario...</option>
                                <option value="administrador" {{ old('tipousuario') == 'administrador' ? 'selected' : '' }}>
                                    Administrador
                                </option>
                                <option value="vendedor" {{ old('tipousuario') == 'vendedor' ? 'selected' : '' }}>
                                    Vendedor
                                </option>
                                <option value="contador" {{ old('tipousuario') == 'contador' ? 'selected' : '' }}>
                                    Contador
                                </option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="6"
                                   autocomplete="new-password"
                                   placeholder="Mínimo 6 caracteres">
                            <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-meter">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Ingresa una contraseña</span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   minlength="6"
                                   autocomplete="new-password"
                                   placeholder="Repite la contraseña">
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>

                    <button type="submit" class="btn-register" id="submitBtn">
                        <i class="fas fa-user-plus"></i>
                        REGISTRARSE
                    </button>
                </form>

                <div class="login-section">
                    <p class="login-text">¿Ya tienes una cuenta?</p>
                    <a href="{{ route('login') }}" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        IR A LOGIN
                    </a>
                </div>
            </div>
        </div>

        
        <div class="image-panel">
            <div class="image-overlay">
                <img src="{{ asset('images/farmacia-registro.jpg') }}" alt="Registro Farmacéuticos" class="hero-image">
                <div class="overlay-content">
                    <div class="stats-card">
                        <h3>Únete al Sistema SEDIM</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <span class="stat-number">+1000</span>
                                    <span class="stat-label">Clientes</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-building"></i>
                                <div>
                                    <span class="stat-number">200+</span>
                                    <span class="stat-label">Farmacias</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-box-open"></i>
                                <div>
                                    <span class="stat-number">1000+</span>
                                    <span class="stat-label">Productos</span>
                                </div>
                            </div>

                        </div>
                        <p>Forma parte de la distribución de productos farmacéuticos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
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

        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = 'Muy débil';
            let color = '#e74c3c';
            
            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 20;
            if (/[a-z]/.test(password)) strength += 20;
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 10;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;
            
            if (strength >= 80) {
                text = 'Muy fuerte';
                color = '#27ae60';
            } else if (strength >= 60) {
                text = 'Fuerte';
                color = '#f39c12';
            } else if (strength >= 40) {
                text = 'Moderada';
                color = '#e67e22';
            } else if (strength >= 20) {
                text = 'Débil';
                color = '#e74c3c';
            }
            
            strengthFill.style.width = strength + '%';
            strengthFill.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });

       
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmation.length > 0) {
                if (password === confirmation) {
                    matchDiv.innerHTML = '<i class="fas fa-check"></i> Las contraseñas coinciden';
                    matchDiv.className = 'password-match success';
                } else {
                    matchDiv.innerHTML = '<i class="fas fa-times"></i> Las contraseñas no coinciden';
                    matchDiv.className = 'password-match error';
                }
            } else {
                matchDiv.innerHTML = '';
                matchDiv.className = 'password-match';
            }
        });

     
        document.querySelector('.register-form').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> REGISTRANDO...';
            submitBtn.disabled = true;
        });

       
        window.addEventListener('load', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (stat.textContent.includes('%')) {
                        stat.textContent = Math.floor(current) + '%';
                    } else if (stat.textContent.includes('+')) {
                        stat.textContent = Math.floor(current).toLocaleString() + '+';
                    } else {
                        stat.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            });
        });
    </script>
</body>
</html>