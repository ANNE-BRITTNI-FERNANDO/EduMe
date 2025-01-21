<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('header')
        
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @if (Session::has('success'))
            <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ Session::get('success') }}
            </div>
        @endif

        @if (Session::has('error'))
            <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                {{ Session::get('error') }}
            </div>
        @endif

        @stack('scripts')

        <!-- jQuery and Toastr JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <script>
            // Toastr configuration
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Cart functionality
            function addToCart(type, id) {
                const route = `/cart/add/${type}/${id}`;
                const button = event.target.closest('button');
                
                // Add loading state
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Adding...
                `;
                
                // Send request with explicit GET method
                fetch(route, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(() => {
                    // Show success message
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50';
                    toast.textContent = 'Added to cart successfully!';
                    document.body.appendChild(toast);
                    
                    // Remove toast after 3 seconds
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                    
                    // Refresh page
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Show error message
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-50';
                    toast.textContent = 'Failed to add to cart. Please try again.';
                    document.body.appendChild(toast);
                    
                    // Remove toast after 3 seconds
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                })
                .finally(() => {
                    // Restore button state
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            }
            
            window.removeFromCart = function(type, id) {
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    const route = type === 'product'
                        ? `/cart/remove/product/${id}`
                        : `/cart/remove/bundle/${id}`;
                    window.location.href = route;
                }
            };
        
            // Show toastr messages
            @if(Session::has('success'))
                toastr.success("{{ Session::get('success') }}");
            @endif
        
            @if(Session::has('error'))
                toastr.error("{{ Session::get('error') }}");
            @endif
        
            @if(Session::has('info'))
                toastr.info("{{ Session::get('info') }}");
            @endif
        
            @if(Session::has('warning'))
                toastr.warning("{{ Session::get('warning') }}");
            @endif
        </script>
        @stack('modals')



        <script>
            function toggleDetails(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.classList.toggle('hidden');
                }
            }
        </script>
    </body>
</html>
