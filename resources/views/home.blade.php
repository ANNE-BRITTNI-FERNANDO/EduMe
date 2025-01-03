<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
 <!-- Navbar -->
 <nav class="fixed top-0 inset-x-0 bg-yellow-700 dark:bg-gray-900  bg-opacity-10 p-4 shadow-lg z-50">
    <div class="flex items-center justify-between max-w-6xl mx-auto px-4">
        <!-- Brand Name or Logo -->
        <a href="/" class="text-2xl font-bold text-white">EduME</a>

        <!-- Navbar Buttons with Circular Borders -->
        <div class="space-x-4 flex items-center">
            @auth
                <a href="{{ route('orders.index') }}" class="text-white hover:text-gray-200 dark:hover:text-gray-400 px-4 py-2 rounded-full border border-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    Orders
                </a>
                @if(auth()->user()->role === 'seller')
                    <a href="{{ route('orders.index', ['view' => 'seller']) }}" class="text-white hover:text-gray-200 dark:hover:text-gray-400 px-4 py-2 rounded-full border border-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m0-3.75h3.75M9 18h3.75" />
                        </svg>
                        Sales
                    </a>
                @endif
            @endauth
            <a href="/login" class="text-white hover:text-gray-200 dark:hover:text-gray-400 px-4 py-2 rounded-full border border-white">
                login
            </a>
            <a href="{{ route('register') }}" class="text-white hover:text-gray-200 dark:hover:text-gray-400 px-4 py-2 rounded-full border border-white">
                signup
            </a>
        </div>
    </div>
</nav>

<body class="flex flex-col items-center">

    <!-- Static Images -->
    <img src="images/hero6.jpeg" alt="Static Image 1" class="w-full max-w-full h-256 object-contain">

    <div class="relative flex justify-center items-center w-28 h-80 m-6 p-4">
        <div class="relative w-80 h-80 flex flex-col justify-center items-center m-6 p-4"> 
            <!-- Main Heading -->
            <h1 class="text-5xl sm:text-6xl md:text-8xl lg:text-9xl font-bold text-center whitespace-nowrap"> <!-- Responsive text size -->
                Join our warm <br> community!
            </h1>
        </div>
    </div>

    <p class="text-center text-gray-700 text-lg sm:text-xl md:text-2xl lg:text-3xl max-w-prose">
        Welcome to “EduME,” your trusted platform for pre-loved study essentials! We believe in giving educational items a second life while making learning accessible and affordable for everyone. Explore our curated selection of high quality, gently used textbooks, stationery, gadgets, and more all at great prices. Join our mission to reduce waste and support sustainable learning, whether you’re a student on a budget or simply looking to declutter your study space! By choosing pre-loved items, you’re not only saving money but also contributing to a more sustainable planet. Each purchase helps us promote recycling and a circular economy, ensuring that fewer resources are wasted. Together, we can create a community that values education and sustainability, making a positive impact on future generations.
    </p>

    <br><br><br>

    <!-- How It Works Section -->
<h2 class="text-3xl md:text-5xl font-semibold text-center text-gray-800 mb-6">
    How It Works...
</h2>

<div class="overflow-auto whitespace-nowrap p-2 flex flex-wrap justify-center">
    <!-- Each step card -->
    <div class="w-full sm:w-1/2 md:w-1/4 inline-block p-2">
        <div class="border border-gray-300 hover:border-gray-700 rounded-lg overflow-hidden">
            <a target="_blank" href="img_lights.jpg">
                <img src="images/step1.png" alt="Step 1" class="w-full h-auto max-w-full object-cover">
            </a>
            <div class="p-4 text-center text-sm md:text-base"><b>sign up to EduME</b></div>
        </div>
    </div>

    <div class="w-full sm:w-1/2 md:w-1/4 inline-block p-2">
        <div class="border border-gray-300 hover:border-gray-700 rounded-lg overflow-hidden">
            <a target="_blank" href="img_mountains.jpg">
                <img src="images/step2.png" alt="Step 2" class="w-full h-auto max-w-full object-cover">
            </a>
            <div class="p-6 text-center text-sm md:text-base"><b>Choose to be a seller,doner or buyer</b></div>
        </div>
    </div>

    <div class="w-full sm:w-1/2 md:w-1/4 inline-block p-2">
        <div class="border border-gray-300 hover:border-gray-700 rounded-lg overflow-hidden">
            <a target="_blank" href="img_mountains.jpg">
                <img src="images/sn3.png" alt="Step 3" class="w-full h-auto max-w-full object-cover">
            </a>
            <div class="p-4 text-center text-sm md:text-base"><b>Now you are all set and good to go!</b></div>
        </div>
    </div>
</div>

<!-- button -->

<a href="/login">
    <button class="bg-blue-900 text-white py-3 px-6 rounded-full font-bold text-lg flex items-center space-x-2 hover:bg-blue-800">
        <span>START NOW</span>
        <span>&#10140;</span> <!-- Arrow icon -->
    </button>
</a>


<footer class="bg-blue-900 text-white py-20 mt-20 relative w-full">
    <div class="w-full mx-auto flex flex-wrap justify-between px-8">

        <!-- Left Section: Quick Links -->
        <div class="flex flex-col items-start w-full md:w-1/3 mb-8">
            <h3 class="text-xl font-bold mb-4">Quick Links</h3>
            <ul class="text-gray-300">
                <li class="mb-2">
                    <a href="#" class="hover:text-gray-100">About Us</a>
                </li>
                <li class="mb-2">
                    <a href="#" class="hover:text-gray-100">FAQs</a>
                </li>
                <li class="mb-2">
                    <a href="#" class="hover:text-gray-100">Privacy Policy</a>
                </li>
                <li>
                    <a href="#" class="hover:text-gray-100">Terms & Conditions</a>
                </li>
            </ul>
        </div>

        <!-- Center Section: Newsletter -->
        <div class="flex flex-col items-start w-full md:w-1/3 mb-8">
            <h3 class="text-xl font-bold mb-4">Stay Updated</h3>
            <p class="mb-4 text-gray-300">Subscribe to our newsletter for the latest updates.</p>
            <form class="flex items-center w-full">
                <input type="email" placeholder="Enter your email" 
                       class="px-4 py-2 w-full rounded-l-lg bg-gray-100 text-gray-800 focus:outline-none">
                <button class="px-6 py-2 bg-indigo-600 rounded-r-lg hover:bg-indigo-700 transition-all">
                    Subscribe
                </button>
            </form>
        </div>

        <!-- Right Section: Contact Information -->
        <div class="flex flex-col space-x-6">
            <h3 class="text-xl font-bold mb-4">Contact Us</h3>
            <ul class="text-gray-300">
                <li class="mb-2 flex items-center">
                    <i class="fas fa-phone-alt mr-2"></i> +1 234 567 890
                </li>
                <li class="mb-2 flex items-center">
                    <i class="fas fa-envelope mr-2"></i> support@edume.com
                </li>
                <li class="flex items-center">
                    <i class="fas fa-map-marker-alt mr-2"></i> 123 Edu St., Learning City
                </li>
            </ul>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="border-t border-white pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center px-8">

            <!-- Left: Navigation Links -->
            <div class="flex items-center space-x-4 mb-4 md:mb-0">
                <a href="#" class="text-lg font-semibold hover:text-gray-300">OUR SERVICES</a>
                <a href="#" class="text-lg font-semibold hover:text-gray-300">HELP</a>
            </div>

            <!-- Center: Copyright -->
            <div class="text-center text-gray-300 text-sm mb-4 md:mb-0">
                All Rights Reserved EduME
            </div>

            <!-- Right: Social Media Icons -->
            <div class="flex space-x-6">
                <a href="#" class="hover:text-gray-300 text-2xl"><i class="fab fa-pinterest"></i></a>
                <a href="#" class="hover:text-gray-300 text-2xl"><i class="fab fa-facebook"></i></a>
                <a href="#" class="hover:text-gray-300 text-2xl"><i class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-gray-300 text-2xl"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </div>

    
</footer>

</body>
</html>
