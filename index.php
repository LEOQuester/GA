<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'backend/config/config.php';
require_once 'backend/includes/auth.php';

$loggedInUser = null;
if (isUserLoggedIn()) {
    $userInfo = getUserInfo();
    $loggedInUser = $userInfo['username'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title> Gaming Arena</title>
    <link rel="icon" href="logo/logo.png" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
</head>

<body>

    <!-- Navigation -->
    <header class="navbar" style="backdrop-filter: blur(10px); background: rgba(0, 0, 0, 0.5);">
        <div class="logo-container">
            <img src="logo/logo.png" class="logo" />
            <span class="brand-name"> Gaming Arena</span>
        </div>
        <nav>
            <a href="#">Home</a>
            <a href="frontend/food.html">Food order</a>
            <a href="frontend/admin/tournment.html">Tournament</a>
            <a href="frontend/admin/shop.html">Shop</a>
            <a href="#services">Services</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <?php if ($loggedInUser): ?>
                <span class="welcome">Welcome, <?= htmlspecialchars($loggedInUser) ?></span>
                <a href="frontend/user/logout.php" class="btn">Logout</a>
            <?php else: ?>
                <a href="frontend/login.php" class="btn">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="parallax-container">
        <img src="image/0006_img.png" class="parallax-layer layer-1" />
        <img src="image/0005.png" class="parallax-layer layer-2" />
        <img src="image/0004.png" class="parallax-layer layer-3" />
        <img src="image/0003.png" class="parallax-layer layer-4" />
        <img src="image/0002.png" class="parallax-layer layer-5" />
        <img src="image/0001.png" class="parallax-layer layer-6" />
        <img src="image/0000.png" class="parallax-layer layer-7"   />
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content" data-aos="fade-down">
            <h1>Level Up Your Game</h1>
            <p>Join us for the ultimate gaming experience</p>
            <?php if ($loggedInUser): ?>
                <a href="frontend/user/dashboard.php" class="btn">Book Now</a>
            <?php else: ?>
                <a href="frontend/login.php" class="btn">Get Started</a>
            <?php endif; ?>
        </div>
    </section>


    <section class="pricing-section" style="padding: 60px 0; background: rgba(34,34,51,0.9); position: relative; overflow: hidden;">
        <h2 style="color:#fff; text-align:center; font-size:2em; margin-bottom: 40px; text-shadow: 0 0 10px #8A2BE2;">Gaming Stations</h2>
        <div class="pricing-carousel">
            <div class="carousel-container">
                <div class="price-card" data-index="0">
                    <div class="card-inner">
                        <h2>🎮 PS5 Room</h2>
                        <p>LKR 400/hour</p>
                        <ul>
                            <li>Private Gaming Room</li>
                            <li>4K HDR Display</li>
                            <li>Premium Controllers</li>
                            <li>Game Library Access</li>
                        </ul>
                    </div>
                </div>
                <div class="price-card" data-index="1">
                    <div class="card-inner">
                        <h2>🕶️ PS4 VR Room</h2>
                        <p>LKR 700/hour</p>
                        <ul>
                            <li>VR Headset Setup</li>
                            <li>Motion Controllers</li>
                            <li>Immersive Games</li>
                            <li>Guided Session</li>
                        </ul>
                    </div>
                </div>
                <div class="price-card" data-index="2">
                    <div class="card-inner">
                        <h2>🖥️ Gaming PCs</h2>
                        <p>LKR 300/hour</p>
                        <ul>
                            <li>RTX Graphics</li>
                            <li>240Hz Monitors</li>
                            <li>Pro Gaming Gear</li>
                            <li>Steam Library</li>
                        </ul>
                    </div>
                </div>
                <div class="price-card" data-index="3">
                    <div class="card-inner">
                        <h2>🖥️ Premium PCs</h2>
                        <p>LKR 300/hour</p>
                        <ul>
                            <li>4090 RTX Setup</li>
                            <li>360Hz Display</li>
                            <li>Stream Ready</li>
                            <li>Premium Peripherals</li>
                        </ul>
                    </div>
                </div>
                <div class="price-card" data-index="4">
                    <div class="card-inner">
                        <h2>🎱 Pool Table</h2>
                        <p>LKR 250/hour</p>
                        <ul>
                            <li>Professional Table</li>
                            <li>Premium Cues</li>
                            <li>Ball Set & Chalk</li>
                            <li>Scoring System</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="carousel-nav">
                <div class="nav-dot active" data-index="0"></div>
                <div class="nav-dot" data-index="1"></div>
                <div class="nav-dot" data-index="2"></div>
                <div class="nav-dot" data-index="3"></div>
                <div class="nav-dot" data-index="4"></div>
            </div>
        </div>

        <style>
            .pricing-carousel {
                width: 100%;
                max-width: 1200px;
                margin: 0 auto;
                position: relative;
            }

            .carousel-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 400px;
                position: relative;
            }

            .price-card {
                position: absolute;
                width: 300px;
                opacity: 0;
                transform: scale(0.8) translateX(100%);
                transition: all 0.5s ease-in-out;
                visibility: hidden;
            }

            .price-card.active {
                opacity: 1;
                transform: scale(1) translateX(0);
                visibility: visible;
                z-index: 2;
            }

            .price-card.prev {
                transform: scale(0.8) translateX(-100%);
                opacity: 0.3;
                visibility: visible;
                z-index: 1;
            }

            .price-card.next {
                transform: scale(0.8) translateX(100%);
                opacity: 0.3;
                visibility: visible;
                z-index: 1;
            }

            .card-inner {
                background: rgba(34, 34, 51, 0.8);
                border: 2px solid #8A2BE2;
                border-radius: 20px;
                padding: 25px;
                text-align: center;
                box-shadow: 0 0 30px rgba(138, 43, 226, 0.4);
                transition: transform 0.3s ease;
            }

            .card-inner:hover {
                transform: translateY(-5px);
            }

            .price-card h2 {
                color: #fff;
                font-size: 24px;
                margin-bottom: 15px;
                text-shadow: 0 0 10px #8A2BE2;
            }

            .price-card p {
                color: #8A2BE2;
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 20px;
            }

            .price-card ul {
                list-style: none;
                padding: 0;
                color: #d1b3ff;
            }

            .price-card ul li {
                margin: 10px 0;
                font-size: 16px;
            }

            .carousel-nav {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-top: 30px;
            }

            .nav-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: rgba(138, 43, 226, 0.3);
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .nav-dot.active {
                background: #8A2BE2;
                box-shadow: 0 0 10px #8A2BE2;
            }

            @media (max-width: 768px) {
                .price-card {
                    width: 280px;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.price-card');
                const dots = document.querySelectorAll('.nav-dot');
                let currentIndex = 0;
                let interval;

                function updateCards() {
                    cards.forEach((card, index) => {
                        card.classList.remove('active', 'prev', 'next');
                        if (index === currentIndex) {
                            card.classList.add('active');
                        } else if (index === (currentIndex - 1 + cards.length) % cards.length) {
                            card.classList.add('prev');
                        } else if (index === (currentIndex + 1) % cards.length) {
                            card.classList.add('next');
                        }
                    });

                    dots.forEach((dot, index) => {
                        dot.classList.toggle('active', index === currentIndex);
                    });
                }

                function nextCard() {
                    currentIndex = (currentIndex + 1) % cards.length;
                    updateCards();
                }

                dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => {
                        currentIndex = index;
                        updateCards();
                        resetInterval();
                    });
                });

                function resetInterval() {
                    clearInterval(interval);
                    interval = setInterval(nextCard, 4000);
                }

                // Initial setup
                updateCards();
                interval = setInterval(nextCard, 4000);

                // Add hover pause functionality
                const carousel = document.querySelector('.pricing-carousel');
                carousel.addEventListener('mouseenter', () => clearInterval(interval));
                carousel.addEventListener('mouseleave', resetInterval);
            });
        </script>
    </section>

    <!-- Image Gallery Section -->
    <section class="image-gallery" style="padding: 40px 0; background: rgba(34,34,51,0.7); overflow: hidden;">
        <h2 style="color:#fff; margin-bottom: 24px; font-size:2em; letter-spacing:1px;">Trending Games</h2>
        <div class="gallery-anim-wrapper" style="width: 100vw; overflow: hidden; position: relative;">
            <div class="gallery-anim-row" style="display: flex; gap: 18px; align-items: center; width: max-content; animation: gallery-scroll 32s linear infinite;">
                <img src="image/game_1.jpg" alt="Gaming Room 1" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_2.jpg" alt="Gaming Room 2" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_3.jpg" alt="Gaming Room 3" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_4.jpg" alt="Gaming Room 4" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_5.jpg" alt="Gaming Room 5" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_6.jpg" alt="Gaming Room 6" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_7.jpg" alt="Gaming Room 7" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <!-- Duplicate for seamless loop -->
                <img src="image/game_1.jpg" alt="Gaming Room 1" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_2.jpg" alt="Gaming Room 2" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_3.jpg" alt="Gaming Room 3" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_4.jpg" alt="Gaming Room 4" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_5.jpg" alt="Gaming Room 5" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_6.jpg" alt="Gaming Room 6" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
                <img src="image/game_7.jpg" alt="Gaming Room 7" style="width: 340px; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 4px 16px #8A2BE2;">
            </div>
        </div>
        <style>
            @keyframes gallery-scroll {
                0% {
                    transform: translateX(0);
                }

                100% {
                    transform: translateX(-50%);
                }
            }

            @media (max-width: 900px) {
                .gallery-anim-row img {
                    width: 220px !important;
                    height: 140px !important;
                }
            }
        </style>
    </section>

    <section class="parallax">
        <video autoplay muted loop playsinline class="bg-video-rotate">
            <source src="video/bg-1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <div class="parallax-content" data-aos="zoom-in" data-aos-duration="1000">
            <h2>Feel the Game</h2>
            <p>High-end gear. Immersive vibes.</p>
        </div>
    </section>


    <!-- Services -->
    <section id="services" class="services">
        <h2 data-aos="fade-up">Our Services</h2>
        <div class="card-container">
            <div class="card" data-aos="fade-right">🎮 Console Gaming</div>
            <div class="card" data-aos="zoom-in">🍔 Snacks & Drinks</div>
            <div class="card" data-aos="fade-left">🛋️ Private Lounges</div>
        </div>
    </section>

    <!-- Lottie Section -->
    <section class="lotties" style="padding: 60px 0; background: linear-gradient(135deg, #18122B 60%, #393053 100%); position: relative;">
        <div style="width:100%;text-align:center;margin-bottom:32px;">
            <h2 style="color:#fff; text-align:center; font-size:2em; letter-spacing:1px; text-shadow:0 0 16px #8A2BE2; margin:0;">Why Choose Us?</h2>
        </div>
        <div class="lottie-creative-wrapper" data-aos="fade-up" style="display: flex; justify-content: center; align-items: stretch; gap: 40px; flex-wrap: wrap;">
            <div style="background: rgba(34,34,51,0.8); border-radius: 24px; box-shadow: 0 0 32px #8A2BE2, 0 0 8px #fff2; padding: 32px 36px; position: relative; border: 2px solid #8A2BE2; min-width: 340px; max-width: 360px; flex:1 1 340px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
                <div style="position: absolute; top: -18px; left: 50%; transform: translateX(-50%); background: #8A2BE2; color: #fff; padding: 4px 18px; border-radius: 12px; font-weight: 600; font-size: 1.1em; box-shadow: 0 2px 8px #8A2BE2; letter-spacing: 1px;">Fun & Vibes</div>
                <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.3.0/dist/dotlottie-wc.js" type="module"></script>
                <dotlottie-wc src="https://lottie.host/083a5cc7-ce4e-459a-8698-28133f07154f/itgf3LDRId.lottie" autoplay loop style="width: 220px; height: 220px;"></dotlottie-wc>
                <div style="margin-top: 18px; color: #d1b3ff; font-size: 1.1em; text-align: center;">Chill, play, and make memories with friends in a vibrant atmosphere.</div>
            </div>
            <div style="background: rgba(34,34,51,0.8); border-radius: 24px; box-shadow: 0 0 32px #8A2BE2, 0 0 8px #fff2; padding: 32px 36px; position: relative; border: 2px solid #8A2BE2; min-width: 340px; max-width: 360px; flex:1 1 340px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
                <div style="position: absolute; top: -18px; left: 50%; transform: translateX(-50%); background: #8A2BE2; color: #fff; padding: 4px 18px; border-radius: 12px; font-weight: 600; font-size: 1.1em; box-shadow: 0 2px 8px #8A2BE2; letter-spacing: 1px;">Pro Gaming</div>
                <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
                <dotlottie-player src="https://lottie.host/c0d5f093-7336-4e92-8073-9441de1e47b9/NasMrNUBgP.lottie" background="transparent" speed="1" style="width: 220px; height: 220px" loop autoplay></dotlottie-player>
                <div style="margin-top: 18px; color: #d1b3ff; font-size: 1.1em; text-align: center;">Top-tier equipment, tournaments, and a community of passionate gamers.</div>
            </div>
            <div style="background: rgba(34,34,51,0.8); border-radius: 24px; box-shadow: 0 0 32px #8A2BE2, 0 0 8px #fff2; padding: 32px 36px; position: relative; border: 2px solid #8A2BE2; min-width: 340px; max-width: 360px; flex:1 1 340px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
                <div style="position: absolute; top: -18px; left: 50%; transform: translateX(-50%); background: #8A2BE2; color: #fff; padding: 4px 18px; border-radius: 12px; font-weight: 600; font-size: 1.1em; box-shadow: 0 2px 8px #8A2BE2; letter-spacing: 1px;">Snacks & Chill</div>
                <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.6.2/dist/dotlottie-wc.js" type="module"></script>
                <dotlottie-wc src="https://lottie.host/18479695-eaf0-43a6-a340-de9781205817/MlO2zJTy2H.lottie" style="width: 300px;height: 300px" speed="1" autoplay loop></dotlottie-wc>
                <div style="margin-top: 18px; color: #d1b3ff; font-size: 1.1em; text-align: center;">Enjoy delicious snacks and drinks while you game or relax in our lounge.</div>
            </div>
        </div>
        <div style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0;">
            <svg width="100%" height="100%" style="position:absolute;left:0;top:0;z-index:0;" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <radialGradient id="glow" cx="50%" cy="50%" r="80%">
                        <stop offset="0%" stop-color="#8A2BE2" stop-opacity="0.18" />
                        <stop offset="100%" stop-color="#18122B" stop-opacity="0" />
                    </radialGradient>
                </defs>
                <ellipse cx="50%" cy="60%" rx="48%" ry="30%" fill="url(#glow)" />
            </svg>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="about">
        <h2 data-aos="fade-up">About Us</h2>
        <p data-aos="fade-right">
            We’re a passionate team offering the coolest space to play, relax, and have fun with friends.
            Whether you're into FIFA, COD, or tournaments — we’ve got you covered.
        </p>
    </section>

    <section id="featured" class="featured-devices">
        <h2 data-aos="fade-up">Featured Devices</h2>
        <div class="device-grid">
            <div class="device-card" data-aos="zoom-in">
                <img src="image/PS5.png" alt="PS5">
                <h3>PlayStation 5</h3>
                <p>Next-gen gaming with ultra-fast loading and immersive visuals.</p>
            </div>

            <div class="device-card" data-aos="zoom-in" data-aos-delay="100">
                <img src="image/xbox.png" alt="Xbox">
                <h3>Xbox Series X</h3>
                <p>4K gaming powerhouse with smooth online multiplayer.</p>
            </div>

            <div class="device-card" data-aos="zoom-in" data-aos-delay="200">
                <img src="image/pc.jpg" alt="Gaming PC">
                <h3>High-End PCs</h3>
                <p>FPS, RPGs, Esports — our rigs can handle it all in 240FPS glory.</p>
            </div>

            <div class="device-card" data-aos="zoom-in" data-aos-delay="300">
                <img src="image/pool.jpeg" alt="Pool Tables">
                <h3>Billiard Tables</h3>
                <p>Take a break and challenge your friends to a relaxing game of pool.</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section id="contact" class="cta" data-aos="fade-up">
        <h2>Ready to Play?</h2> <br>
        <?php if ($loggedInUser): ?>
            <a href="frontend/user/dashboard.php" class="btn">Join the Battle</a>
        <?php else: ?>
            <a href="frontend/login.php" class="btn">Join the Battle</a>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Gaming Arena | <a href="https://wa.me/94771234567">WhatsApp Us</a></p>
    </footer>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script src="script.js"></script>

</body>

</html>