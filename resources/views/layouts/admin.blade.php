<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - BrandThirty</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-black': '#050505',
                        'brand-dark': '#111111',
                        'brand-red': '#FF2D46',
                        'brand-red-hover': '#d91b32',
                        'brand-gray': '#1F1F1F',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #111;
        }

        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body class="bg-brand-black text-white antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside
        class="w-64 bg-brand-dark border-r border-white/5 flex flex-col justify-between hidden md:flex flex-shrink-0">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="h-20 flex items-center px-8 border-b border-white/5 flex-shrink-0">
                <img class="h-8 w-auto object-contain brightness-100" src="{{ asset('Images/B30_logo-04.png') }}"
                    alt="BrandThirty">
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ url('admin') }}"
                    class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin') && !request()->has('status') ? 'bg-brand-red text-white shadow-lg shadow-red-900/20' : 'text-gray-400 hover:text-white hover:bg-white/5' }} rounded-xl transition">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="font-medium">Overview</span>
                </a>

                <a href="{{ url('admin/customers') }}"
                    class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/customers') ? 'bg-brand-red text-white shadow-lg shadow-red-900/20' : 'text-gray-400 hover:text-white hover:bg-white/5' }} rounded-xl transition">
                    <i class="fas fa-users w-5"></i>
                    <span class="font-medium">Customers</span>
                </a>

                <a href="{{ url('admin/orders') }}"
                    class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/orders') ? 'bg-brand-red text-white shadow-lg shadow-red-900/20' : 'text-gray-400 hover:text-white hover:bg-white/5' }} rounded-xl transition">
                    <i class="fas fa-shopping-bag w-5"></i>
                    <span class="font-medium">Orders</span>
                </a>

                <div class="pt-4 border-t border-white/5 mt-4">
                    <div class="text-xs uppercase text-gray-600 font-bold px-4 mb-2">Settings</div>
                    <a href="{{ url('admin/settings') }}"
                        class="flex items-center gap-3 px-4 py-3 {{ request()->is('admin/settings') ? 'bg-brand-red text-white shadow-lg shadow-red-900/20' : 'text-gray-400 hover:text-white hover:bg-white/5' }} rounded-xl transition">
                        <i class="fas fa-cog w-5"></i>
                        <span class="font-medium">Settings</span>
                    </a>
                    <button onclick="window.location.href='{{ url('admin/logout') }}'"
                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span class="font-medium">Logout</span>
                    </button>
                </div>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t border-white/5 flex-shrink-0">
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="w-8 h-8 rounded-full bg-brand-red flex items-center justify-center font-bold">A</div>
                    <div>
                        <div class="text-sm font-bold">Admin</div>
                        <div class="text-xs text-gray-500">Super User</div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Mobile Header -->
        <header
            class="h-16 bg-brand-dark border-b border-white/5 flex md:hidden items-center justify-between px-4 flex-shrink-0">
            <img class="h-6 w-auto" src="{{ asset('Images/B30_logo-04.png') }}" alt="BrandThirty">
            <button onclick="confirmLogout()" class="text-gray-400"><i class="fas fa-sign-out-alt"></i></button>
        </header>

        <!-- Main Content Scrollable Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-8 relative">
            @if(session('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: "{{ session('success') }}",
                            background: '#111',
                            color: '#fff',
                            confirmButtonColor: '#FF2D46'
                        });
                    });
                </script>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Are you sure you want to end your session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF2D46',
                cancelButtonColor: '#333',
                confirmButtonText: 'Yes, Logout',
                background: '#111',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ url('admin/logout') }}";
                }
            })
        }
    </script>
    @yield('scripts')
</body>

</html>