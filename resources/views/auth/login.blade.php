<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ChatHub Team</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    :root {
        --teams-purple: #6264a7;
        --teams-dark: #464775;
        --soft-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    body, html {
        height: 100%;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        overflow: hidden !important;
    }

    .login-container {
        display: flex;
        height: 100vh;
        width: 100vw;
    }

    /* Creative Quote Side */
    .quote-side {
        flex: 1.2;
        background: linear-gradient(135deg, var(--teams-purple) 0%, var(--teams-dark) 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        padding: 60px;
        position: relative;
        overflow: hidden;
    }

    /* Floating Circle Shapes for Creativity */
    .quote-side::before {
        content: "";
        position: absolute;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        top: -150px;
        left: -150px;
    }

    .quote-side::after {
        content: "";
        position: absolute;
        width: 250px;
        height: 250px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        bottom: -50px;
        right: -50px;
    }

    .quote-content {
        z-index: 1;
        max-width: 500px;
        text-align: center;
        animation: fadeIn 1.2s ease-out;
    }

    .quote-icon {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 20px;
    }

    /* Login Form Side */
    .form-side {
        flex: 1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }

    .login-card {
        width: 100%;
        max-width: 380px;
        animation: slideUp 0.8s ease-out;
    }

    .brand-logo {
        color: var(--teams-purple);
        font-size: 3rem;
        margin-bottom: 10px;
        transition: transform 0.3s ease;
    }

    .brand-logo:hover {
        transform: rotate(-10deg) scale(1.1);
    }

    /* Floating Button Magic */
    .btn-teams {
        background-color: var(--teams-purple);
        border: none;
        color: white;
        padding: 12px;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-teams:hover {
        background-color: var(--teams-dark);
        color: white;
        transform: translateY(-4px); /* Asli float effect */
        box-shadow: 0 8px 20px rgba(98, 100, 167, 0.4); /* Depth shadow */
    }

    .btn-teams:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(98, 100, 167, 0.3);
    }

    /* Input Focus Effects */
    .form-control {
        padding: 12px;
        border-radius: 0 6px 6px 0;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
    }

    .input-group {
        border-radius: 6px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .input-group:focus-within {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .form-control:focus {
        border-color: var(--teams-purple);
        box-shadow: none;
    }

    .input-group-text {
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #ddd;
        border-right: none;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 992px) {
        .quote-side { display: none; }
        .form-side { background-color: #f8f9fa; }
    }
</style>
</head>
<body>

    <div class="login-container">
        
        <div class="quote-side d-none d-lg-flex">
            <div class="quote-content">
                <i class="bi bi-chat-left-quote quote-icon"></i>
                <h1 class="fw-bold mb-4" style="font-size: 2.5rem; line-height: 1.2;">
                    "Technology is best when it brings people together."
                </h1>
                <p class="fs-5 opacity-75">— Matt Mullenweg</p>
                <div class="mt-5">
                    <span class="badge rounded-pill bg-white text-dark px-3 py-2 opacity-50">v2.0.26 Beta</span>
                </div>
            </div>
        </div>

        <div class="form-side">
            <div class="login-card">
                <div class="text-center mb-5">
                    <div class="brand-logo">
                        <i class="bi bi-hash"></i>
                    </div>
                    <h2 class="fw-bold text-dark">ChatHub</h2>
                    <p class="text-muted">Sign in to your team account</p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger py-2 small border-0 shadow-sm">{{ session('error') }}</div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email address</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="name@company.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-teams w-100 mb-4 shadow-sm">Sign in</button>                   
                </form>

                <div class="text-center mt-5">
                    <p style="font-size: 0.75rem; color: #aaa;">© 2026 ChatHub Team. Built for professional collaboration.</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>