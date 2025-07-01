<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to ChatSphere, a platform to connect instantly with friends and colleagues.">
    <meta name="keywords" content="ChatSphere, login, chat app, communication">
    <title>ChatSphere - Login Portal</title>
    <script>
        // Fallback for GSAP if CDN fails
        window.gsap = window.gsap || {};
        window.gsap.plugins = window.gsap.plugins || {};
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" onerror="document.write('<script src=\"js/gsap.min.js\"><\/script>')"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/TextPlugin.min.js" onerror="document.write('<script src=\"js/TextPlugin.min.js\"><\/script>')"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            overflow: hidden;
            position: relative;
        }
        .container {
            text-align: center;
            color: white;
            z-index: 20;
            padding: 0 1.5rem;
            max-width: 90%;
        }
        h1 {
            font-size: clamp(2rem, 8vw, 3.8rem);
            margin-bottom: 1rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        p {
            font-size: clamp(1rem, 4vw, 1.6rem);
            margin-bottom: 1.5rem;
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }
        .cursor {
            display: inline-block;
            font-weight: bold;
            animation: blink 0.7s step-end infinite;
        }
        @keyframes blink {
            50% { opacity: 0; }
        }
        .button-group {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .login-btn {
            padding: 0.8rem 2rem;
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            font-weight: bold;
            color:rgb(255, 255, 255);
            background: none;
            border: 2px solid rgb(255, 255, 255);
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease;
            position: relative;
            overflow: hidden;
            transform: perspective(500px);
            min-width: 150px;
        }
        .login-btn:hover {
            background: #ecf0f1;
            color: #2c3e50;
        }
        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }
        .login-btn:hover::before {
            width: 200px;
            height: 200px;
        }
        .animate-text, .animate-btn {
            opacity: 1; /* Fallback for visibility */
        }
        /* Background Elements */
        .wave {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2), transparent);
            opacity: 0.3;
            z-index: 2;
        }
        .big-circle {
            position: absolute;
            width: clamp(100px, 20vw, 200px);
            height: clamp(100px, 20vw, 200px);
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent);
            border-radius: 50%;
            pointer-events: none;
            z-index: 3;
            will-change: transform, opacity;
        }
        .particle {
            position: absolute;
            width: clamp(8px, 1vw, 12px);
            height: clamp(8px, 1vw, 12px);
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            z-index: 4;
            will-change: transform;
        }
        .chat-bubble {
            position: absolute;
            width: clamp(20px, 4vw, 40px);
            height: clamp(20px, 4vw, 40px);
            background: url('#chat-bubble') center/cover;
            pointer-events: none;
            z-index: 5;
            will-change: transform, opacity;
        }
        .typing-indicator {
            position: absolute;
            width: clamp(30px, 5vw, 50px);
            height: clamp(20px, 3vw, 30px);
            background: url('#typing-indicator') center/cover;
            pointer-events: none;
            z-index: 6;
            will-change: transform, opacity;
        }
        .mockup {
            position: absolute;
            width: clamp(40%, 50vw, 50%);
            height: clamp(40%, 50vw, 50%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" fill="rgba(255,255,255,0.1)"><rect x="50" y="50" width="500" height="300" rx="20" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="4"/><text x="300" y="200" font-family="Arial" font-size="40" fill="rgba(255,255,255,0.3)" text-anchor="middle">Chat-App</text></svg>') center/cover;
            opacity: 0.1;
            z-index: 1;
        }
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            h1 {
                font-size: clamp(1.8rem, 7vw, 3rem);
            }
            p {
                font-size: clamp(0.9rem, 3.5vw, 1.4rem);
            }
            .button-group {
                flex-direction: column;
                gap: 1rem;
            }
            .login-btn {
                padding: 0.6rem 1.5rem;
                min-width: 120px;
            }
        }
        @media (max-width: 480px) {
            h1 {
                font-size: clamp(1.5rem, 6vw, 2.5rem);
            }
            p {
                font-size: clamp(0.8rem, 3vw, 1.2rem);
            }
            .mockup, .big-circle {
                width: 80%;
                height: 80%;
            }
        }
    </style>
</head>
<body>
    <!-- SVG Sprite -->
    <svg style="display: none;">
        <symbol id="chat-bubble" viewBox="0 0 24 24">
            <path fill="rgba(255,255,255,0.5)" d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
        </symbol>
        <symbol id="typing-indicator" viewBox="0 0 24 24">
            <circle fill="rgba(255,255,255,0.4)" cx="4" cy="12" r="2"/>
            <circle fill="rgba(255,255,255,0.4)" cx="12" cy="12" r="2"/>
            <circle fill="rgba(255,255,255,0.4)" cx="20" cy="12" r="2"/>
        </symbol>
    </svg>

    <!-- Background Elements -->
    <div class="mockup"></div>
    <div class="wave"></div>
    <div class="big-circle" id="circle1"></div>
    <div class="big-circle" id="circle2"></div>
    <div class="big-circle" id="circle3"></div>
    <div class="particle" id="particle1"></div>
    <div class="particle" id="particle2"></div>
    <div class="particle" id="particle3"></div>
    <div class="particle" id="particle4"></div>
    <div class="particle" id="particle5"></div>
    <div class="chat-bubble" id="bubble1"></div>
    <div class="chat-bubble" id="bubble2"></div>
    <div class="chat-bubble" id="bubble3"></div>
    <div class="typing-indicator" id="typing1"></div>
    <div class="typing-indicator" id="typing2"></div>

    <!-- Main Content -->
    <div class="container">
        <h1 class="animate-text">Welcome to Chat - App</h1>
        <p class="animate-text typing">Connect instantly. Choose your login.<span class="cursor" aria-hidden="true">|</span></p>
        <div class="button-group">
            <button class="login-btn animate-btn" aria-label="Log in as Administrator" data-original-text="Admin Login" onclick="redirectTo('admin-login.php')">Admin Login</button>
            <button class="login-btn animate-btn" aria-label="Log in as User" data-original-text="User Login" onclick="redirectTo('login.php')">User Login</button>
        </div>
    </div>

    <script>
        // Redirect with validation
        async function redirectTo(url) {
            try {
                const response = await fetch(url, { method: 'HEAD' });
                if (response.ok) {
                    window.location.href = url;
                } else {
                    alert('Page not found. Please try again later.');
                }
            } catch (error) {
                console.error('Redirect error:', error);
                alert('Unable to connect. Please check your network.');
            }
        }

        // Check GSAP and TextPlugin loading
        if (typeof gsap === 'undefined') {
            console.error('GSAP not loaded. Check CDN or include GSAP locally.');
            document.querySelectorAll('.animate-text, .animate-btn').forEach(el => {
                el.style.opacity = 1;
            });
        } else if (typeof gsap.plugins.text === 'undefined') {
            console.error('GSAP TextPlugin not loaded. Falling back to static text.');
            document.querySelector('.typing').innerHTML = 'Connect instantly. Choose your login.<span class="cursor" aria-hidden="true">|</span>';
        } else {
            console.log('GSAP and TextPlugin loaded successfully.');

            // Animation Configuration
            const animationConfig = {
                text: { duration: 1.2, y: 60, ease: "power4.out", stagger: 0.3 },
                button: { duration: 1.2, scale: 0.7, ease: "elastic.out(1, 0.4)", stagger: 0.4, delay: 0.8 },
                wave: { scale: 1.3, opacity: 0.2, duration: 3.5, ease: "sine.inOut" },
                circle: { baseDuration: 6, stagger: 2, delay: 0.5, ease: "sine.inOut" },
                particle: { baseDuration: 4, stagger: 1.5, delay: 0.4, ease: "sine.inOut" },
                bubble: { duration: 5, stagger: 1.5, delay: 0.8, ease: "linear" },
                typing: { duration: 4, stagger: 2, delay: 1.2, ease: "linear" }
            };

            // Text Animation
            gsap.from(".animate-text", animationConfig.text);

            // Typing Effect with Cursor
            const subtitle = document.querySelector('.typing');
            const text = subtitle.textContent;
            subtitle.textContent = '';
            gsap.to(subtitle, {
                duration: 2.5,
                text: text,
                ease: "none",
                delay: 1.2,
                onComplete: () => {
                    subtitle.innerHTML = text + '<span class="cursor" aria-hidden="true">|</span>';
                }
            });

            // Button Animations
            gsap.from(".animate-btn", animationConfig.button);

            // Button Hover, Click, and Keyboard Effects
            document.querySelectorAll(".login-btn").forEach(button => {
                gsap.set(button, { transformPerspective: 500 });
                button.setAttribute("tabindex", "0");

                button.addEventListener("mouseenter", () => {
                    gsap.to(button, {
                        scale: 1.15,
                        rotateX: 15,
                        boxShadow: "0 10px 25px rgba(0, 0, 0, 0.4)",
                        duration: 0.4,
                        ease: "power2.out"
                    });
                });
                button.addEventListener("mouseleave", () => {
                    gsap.to(button, {
                        scale: 1,
                        rotateX: 0,
                        boxShadow: "0 4px 15px rgba(0, 0, 0, 0.2)",
                        duration: 0.4,
                        ease: "power2.out"
                    });
                });
                button.addEventListener("click", () => {
                    button.textContent = "Loading...";
                    gsap.to(button, {
                        scale: 0.9,
                        rotateY: 10,
                        duration: 0.2,
                        ease: "power1.in",
                        yoyo: true,
                        repeat: 1,
                        onComplete: () => {
                            button.textContent = button.dataset.originalText;
                            const url = button.getAttribute("onclick").match(/'([^']+)'/)[1];
                            redirectTo(url);
                        }
                    });
                });
                button.addEventListener("keydown", (e) => {
                    if (e.key === "Enter" || e.key === " ") {
                        e.preventDefault();
                        button.click();
                    }
                });
            });

            // Background Gradient Animation
            gsap.to("body", {
                background: "linear-gradient(135deg, #2c3e50, #e84393)",
                duration: 6,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut"
            });

            // Mockup Animation
            gsap.to(".mockup", {
                scale: 1.1,
                opacity: 0.15,
                duration: 5,
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut"
            });

            // Wave Animation
            gsap.to(".wave", animationConfig.wave);

            // Big Circle Animations
            const circles = ["#circle1", "#circle2", "#circle3"];
            circles.forEach((circle, index) => {
                gsap.to(circle, {
                    x: () => Math.random() * window.innerWidth,
                    y: () => Math.random() * window.innerHeight,
                    opacity: 0.25,
                    duration: animationConfig.circle.baseDuration + index * animationConfig.circle.stagger,
                    repeat: -1,
                    yoyo: true,
                    ease: animationConfig.circle.ease,
                    delay: index * animationConfig.circle.delay,
                    onRepeat: () => {
                        gsap.set(circle, {
                            x: Math.random() * window.innerWidth,
                            y: Math.random() * window.innerHeight
                        });
                    }
                });
            });

            // Particle Animations
            const particles = ["#particle1", "#particle2", "#particle3", "#particle4", "#particle5"];
            particles.forEach((particle, index) => {
                gsap.to(particle, {
                    x: () => Math.random() * window.innerWidth,
                    y: () => Math.random() * window.innerHeight,
                    duration: animationConfig.particle.baseDuration + index * animationConfig.particle.stagger,
                    repeat: -1,
                    yoyo: true,
                    ease: animationConfig.particle.ease,
                    delay: index * animationConfig.particle.delay,
                    onRepeat: () => {
                        gsap.set(particle, {
                            x: Math.random() * window.innerWidth,
                            y: Math.random() * window.innerHeight
                        });
                    }
                });
            });

            // Chat Bubble Parallax Animations
            const bubbles = ["#bubble1", "#bubble2", "#bubble3"];
            bubbles.forEach((bubble, index) => {
                gsap.fromTo(bubble, {
                    x: () => Math.random() * window.innerWidth,
                    y: window.innerHeight,
                    opacity: 0
                }, {
                    y: -window.innerHeight,
                    x: () => Math.random() * window.innerWidth + (index % 2 ? 50 : -50),
                    opacity: 0.7,
                    duration: animationConfig.bubble.duration + index * animationConfig.bubble.stagger,
                    repeat: -1,
                    ease: animationConfig.bubble.ease,
                    delay: index * animationConfig.bubble.delay,
                    onRepeat: () => {
                        gsap.set(bubble, {
                            x: Math.random() * window.innerWidth,
                            y: window.innerHeight,
                            opacity: 0
                        });
                    }
                });
            });

            // Typing Indicator Animations
            const typingIndicators = ["#typing1", "#typing2"];
            typingIndicators.forEach((indicator, index) => {
                gsap.fromTo(indicator, {
                    x: () => Math.random() * window.innerWidth,
                    y: window.innerHeight,
                    opacity: 0
                }, {
                    y: -window.innerHeight,
                    opacity: 0.5,
                    duration: animationConfig.typing.duration + index * animationConfig.typing.stagger,
                    repeat: -1,
                    ease: animationConfig.typing.ease,
                    delay: index * animationConfig.typing.delay,
                    onRepeat: () => {
                        gsap.set(indicator, {
                            x: Math.random() * window.innerWidth,
                            y: window.innerHeight,
                            opacity: 0
                        });
                    }
                });
            });
        }
    </script>
</body>
</html>