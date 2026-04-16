<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ChatHub Team</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #6264a7 0%, #464775 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: none;
        }
        .brand-logo {
            color: #6264a7;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .btn-teams {
            background-color: #6264a7;
            border: none;
            color: white;
            padding: 10px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-teams:hover {
            background-color: #464775;
            color: white;
        }
        .form-control:focus {
            border-color: #6264a7;
            box-shadow: 0 0 0 0.25 cold;
            box-shadow: 0 0 0 0.25rem rgba(98, 100, 167, 0.25);
        }
        .footer-text {
            font-size: 0.8rem;
            color: #666;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <div class="brand-logo">
            <i class="bi bi-hash"></i>
        </div>
        <h4 class="fw-bold mb-1">ChatHub</h4>
        <p class="text-muted mb-4">Sign in to your team account</p>

        @if(session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="text-start">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold">Email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0" placeholder="name@company.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-teams w-100 mb-3">Sign in</button>
            
            <div class="d-flex justify-content-between">
                <!-- <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div> -->
                <!-- <a href="#" class="small text-decoration-none" style="color: #6264a7;">Forgot password?</a> -->
            </div>
        </form>

        <div class="footer-text">
            © 2026 ChatHub Team. All rights reserved.
        </div>
    </div>

</body>
</html>