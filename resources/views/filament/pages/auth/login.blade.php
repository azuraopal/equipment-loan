<div>
    <style>
        /* ===== Login Page Custom Styles ===== */
        :root {
            --login-gradient-1: #f59e0b;
            --login-gradient-2: #d97706;
            --login-gradient-3: #b45309;
            --login-gradient-4: #ea580c;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
        }

        /* ===== Left Branding Panel ===== */
        .login-brand-panel {
            display: none;
            width: 50%;
            background: linear-gradient(135deg, var(--login-gradient-1) 0%, var(--login-gradient-2) 30%, var(--login-gradient-3) 70%, var(--login-gradient-4) 100%);
            position: relative;
            overflow: hidden;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
        }

        .login-brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(0, 0, 0, 0.05) 0%, transparent 70%);
            z-index: 1;
        }

        /* Floating geometric shapes */
        .login-brand-panel .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            z-index: 0;
        }

        .login-brand-panel .shape-1 {
            width: 300px;
            height: 300px;
            background: #fff;
            top: -80px;
            right: -60px;
            animation: float-shape 8s ease-in-out infinite;
        }

        .login-brand-panel .shape-2 {
            width: 200px;
            height: 200px;
            background: #fff;
            bottom: -40px;
            left: -40px;
            animation: float-shape 6s ease-in-out infinite reverse;
        }

        .login-brand-panel .shape-3 {
            width: 120px;
            height: 120px;
            background: #fff;
            top: 40%;
            left: 10%;
            animation: float-shape 10s ease-in-out infinite 2s;
        }

        @keyframes float-shape {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        .brand-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }

        .brand-icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: icon-float 4s ease-in-out infinite;
        }

        @keyframes icon-float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .brand-icon-wrapper svg {
            width: 60px;
            height: 60px;
            color: #fff;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .brand-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.6;
            max-width: 360px;
            margin: 0 auto;
        }

        .brand-features {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .brand-feature:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
        }

        .brand-feature svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            opacity: 0.9;
        }

        /* ===== Right Form Panel ===== */
        .login-form-panel {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1.5rem;
            background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 30%, #fde68a 100%);
            position: relative;
            overflow: hidden;
        }

        .login-form-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, rgba(245, 158, 11, 0.05) 1px, transparent 0);
            background-size: 40px 40px;
            z-index: 0;
        }

        .login-form-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.05),
                0 10px 15px -3px rgba(0, 0, 0, 0.08),
                0 20px 40px -10px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .login-card-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-mobile-icon {
            display: flex;
            width: 64px;
            height: 64px;
            margin: 0 auto 1.25rem;
            background: linear-gradient(135deg, var(--login-gradient-1), var(--login-gradient-4));
            border-radius: 16px;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .login-mobile-icon svg {
            width: 32px;
            height: 32px;
            color: #fff;
        }

        .login-card-title {
            font-size: 1.625rem;
            font-weight: 700;
            color: #1c1917;
            letter-spacing: -0.02em;
            margin: 0 0 0.375rem;
        }

        .login-card-subtitle {
            font-size: 0.9rem;
            color: #78716c;
            margin: 0;
        }

        /* Form styling overrides */
        .login-card .fi-fo-field-wrp {
            margin-bottom: 0.25rem;
        }

        .login-card .fi-btn-primary {
            border-radius: 12px !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
        }

        .login-card .fi-btn-primary:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4) !important;
        }

        .login-card .fi-btn-primary:active {
            transform: translateY(0) !important;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #a8a29e;
        }

        /* ===== Responsive: Desktop ===== */
        @media (min-width: 1024px) {
            .login-brand-panel {
                display: flex;
            }

            .login-form-panel {
                width: 50%;
                background: linear-gradient(180deg, #fefce8 0%, #fef9c3 50%, #fef3c7 100%);
            }

            .login-mobile-icon {
                display: none;
            }

            .login-card {
                padding: 3rem 2.5rem;
            }
        }

        /* ===== Dark Mode Support ===== */
        .dark .login-form-panel {
            background: linear-gradient(180deg, #1c1917 0%, #292524 50%, #1c1917 100%);
        }

        .dark .login-form-panel::before {
            background-image: radial-gradient(circle at 1px 1px, rgba(245, 158, 11, 0.03) 1px, transparent 0);
        }

        .dark .login-card {
            background: rgba(41, 37, 36, 0.8);
            border-color: rgba(68, 64, 60, 0.5);
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.2),
                0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }

        .dark .login-card-title {
            color: #fafaf9;
        }

        .dark .login-card-subtitle {
            color: #a8a29e;
        }

        .dark .login-footer {
            color: #57534e;
        }
    </style>

    <div class="login-wrapper">
        {{-- Left Branding Panel --}}
        <div class="login-brand-panel">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>

            <div class="brand-content">
                <div class="brand-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" />
                    </svg>
                </div>

                <h1 class="brand-title">EquipLoan</h1>
                <p class="brand-subtitle">Sistem Manajemen Peminjaman Peralatan yang Modern & Terintegrasi</p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Kelola peminjaman dengan mudah
                    </div>
                    <div class="brand-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Pantau status real-time
                    </div>
                    <div class="brand-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Laporan & analitik lengkap
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Form Panel --}}
        <div class="login-form-panel">
            <div class="login-form-container">
                <div class="login-card">
                    <div class="login-card-header">
                        <div class="login-mobile-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" />
                            </svg>
                        </div>

                        <h2 class="login-card-title">Selamat Datang</h2>
                        <p class="login-card-subtitle">Masuk ke akun Anda untuk melanjutkan</p>
                    </div>

                    {{-- Filament Login Form Content --}}
                    {{ $this->content }}
                </div>

                <div class="login-footer">
                    &copy; {{ date('Y') }} EquipLoan &mdash; All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>