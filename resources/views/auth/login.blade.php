<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang Di Imanuel Store</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/images/icon_imanuel.png') }}">
    <style>
        /* === LOGIN PAGE STYLE - IMANUEL PET SUPPLY === */

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f4e2d8, #ba8b02);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Container */
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            animation: fadeIn 1s ease-in;
        }

        /* Card */
        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 2.5rem;
            width: 90%;
            max-width: 420px;
            text-align: center;
            animation: slideUp 0.8s ease-out;
            transition: all 0.3s ease;
        }

        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.2);
        }

        /* Logo Area */
        .logo-container {
            margin-bottom: 1.8rem;
            animation: fadeIn 1.2s ease-in;
        }

        .logo {
            width: 150px;
            height: 150px;
            object-fit: contain;
            filter: drop-shadow(0 3px 5px rgba(0,0,0,0.2));
        }

        .brand-name {
            font-size: 1.9rem;
            font-weight: 700;
            color: #6b3e04;
            letter-spacing: 1px;
            margin-top: 0.6rem;
        }

        .brand-sub {
            font-size: 0.9rem;
            color: #b07916;
        }

        /* Status Message */
        .status-message {
            background-color: #fff4e0;
            border: 1px solid #f1ca89;
            color: #7a5103;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            animation: fadeIn 1s ease;
        }

        /* Form Inputs */
        .form-group {
            text-align: left;
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-weight: 500;
            color: #5a3900;
            margin-bottom: 0.3rem;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d9b382;
            border-radius: 10px;
            outline: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #b07916;
            box-shadow: 0 0 8px rgba(176, 121, 22, 0.3);
        }

        /* Error Text */
        .error {
            color: #d44b3e;
            font-size: 0.8rem;
            margin-top: 0.3rem;
            display: block;
        }

        /* Remember me */
        .remember-me {
            display: flex;
            align-items: center;
            justify-content: start;
            margin-top: 0.5rem;
        }

        .remember-me label {
            font-size: 0.85rem;
            color: #5a3900;
        }

        .remember-me input {
            margin-right: 0.5rem;
        }

        /* Actions */
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: #b07916;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #6b3e04;
        }

        /* Button */
        .login-button {
            background: linear-gradient(90deg, #b07916, #6b3e04);
            color: white;
            padding: 0.7rem 1.7rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-button:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #6b3e04, #b07916);
        }

        /* Footer */
        .footer {
            font-size: 0.8rem;
            color: #9c6f27;
            margin-top: 2rem;
        }

        /* === Animations === */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="logo-container">
                <img src="{{ asset('assets/images/icon_imanuel2.png') }}" alt="Imanuel Logo" class="logo">
                <p class="brand-sub">Pangan & Kebutuhan Hewan</p>
            </div>

            @if (session('status'))
                <div class="status-message">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                    @error('password')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="remember-me">
                    <label>
                        <input type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <div class="actions">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                    @endif
                    <button type="submit" class="login-button">Masuk</button>
                </div>
            </form>

            <p class="footer">&copy; {{ date('Y') }} Imanuel Pet Supply. All rights reserved.</p>
        </div>
    </div>

</body>
</html>