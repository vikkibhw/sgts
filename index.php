
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGTS | Smart Goods Transport System</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
:root {
    --navy: #06264a;
    --blue: #0d4276;
    --orange: #ff8a1d;
    --light: #f3f8ff;
    --text: #142b46;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
    font-family: 'Inter', Arial, sans-serif;
    color: var(--text);
    background: var(--light);
}
a { text-decoration: none; }
.container { width: 90%; max-width: 1250px; margin: auto; }

/* NAVBAR */
.navbar {
    background: #052343;
    color: white;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 18px #00172d35;
}
.nav-inner {
    min-height: 82px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}
.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    min-width: 220px;
}
.logo-icon {
    width: 53px;
    height: 53px;
    border-radius: 15px;
    background: linear-gradient(135deg, #ffae47, #ff7911);
    display: grid;
    place-items: center;
    color: #08294d;
    font-size: 27px;
    box-shadow: 0 5px 15px #ff8a1d35;
}
.logo-name { font-size: 28px; font-weight: 800; letter-spacing: 1px; }
.logo-name span { color: var(--orange); }
.logo small { display: block; font-size: 10px; color: #c7d9ed; margin-top: 2px; }

.nav-links { display: flex; align-items: center; gap: 7px; }
.nav-links a {
    color: #e6effa;
    padding: 12px 14px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    transition: .25s;
}
.nav-links a:hover, .nav-links a.active {
    background: var(--orange);
    color: #06264a;
}
.nav-actions { display: flex; align-items: center; gap: 10px; }
.btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 9px;
    padding: 13px 22px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    transition: .25s;
    cursor: pointer;
    border: none;
}
.btn-orange { background: var(--orange); color: #082747; }
.btn-orange:hover { background: #ffab4b; transform: translateY(-2px); }
.btn-outline { border: 1px solid #9db5d0; color: white; }
.btn-outline:hover { background: white; color: var(--navy); }
.menu-toggle { display: none; color: white; font-size: 24px; cursor: pointer; }

/* HERO */
.hero {
    min-height: 480px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    color: white;
    background:
      linear-gradient(90deg, #041f3df5 0%, #062b4bdc 39%, #062b4b30 72%, #062b4b12 100%),
      url('https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=2000&q=85') center 55%/cover;
}
.hero-content { position: relative; z-index: 1; max-width: 620px; padding: 65px 0; }
.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ff8a1d20;
    border: 1px solid #ff9a3c70;
    color: #ffc17e;
    padding: 9px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 22px;
}
.hero h1 { font-size: clamp(38px, 5vw, 62px); line-height: 1.08; font-weight: 800; letter-spacing: -2px; }
.hero h1 span { color: var(--orange); }
.hero h2 { color: #a7dfff; font-size: 18px; margin-top: 15px; }
.hero p { color: #e2edf8; line-height: 1.8; margin: 16px 0 26px; max-width: 540px; font-size: 15px; }
.hero-buttons { display: flex; flex-wrap: wrap; gap: 12px; }
.hero .btn { padding: 15px 25px; }
.hero-note { display: flex; gap: 22px; flex-wrap: wrap; margin-top: 30px; color: #e6eff9; font-size: 12px; }
.hero-note i { color: #ffab4b; margin-right: 6px; }

/* TRUST BAR */
.trust-bar { background: white; box-shadow: 0 8px 25px #09294b0a; }
.trust-grid { display: grid; grid-template-columns: repeat(4,1fr); padding: 22px 0; }
.trust-item { display: flex; align-items: center; gap: 13px; padding: 0 20px; border-right: 1px solid #e2eaf3; }
.trust-item:first-child { padding-left: 0; }
.trust-item:last-child { border: none; }
.trust-icon {
    width: 47px; height: 47px; flex-shrink: 0; display: grid;
    place-items: center; border-radius: 50%; background: #fff0e2;
    color: #f57b10; font-size: 20px;
}
.trust-item strong { display: block; font-size: 13px; margin-bottom: 5px; }
.trust-item small { color: #73859a; font-size: 11px; }

/* SERVICES */
.section { padding: 70px 0; }
.section-heading { display: flex; justify-content: space-between; align-items: end; gap: 20px; margin-bottom: 28px; }
.kicker { color: #f0780b; font-size: 12px; font-weight: 800; letter-spacing: 1.5px; margin-bottom: 10px; }
.section-heading h2 { font-size: clamp(28px, 3vw, 38px); color: var(--navy); letter-spacing: -1px; }
.section-heading p { margin-top: 9px; color: #708196; font-size: 14px; }
.text-link { color: #e8750a; font-size: 13px; font-weight: 700; white-space: nowrap; }
.text-link i { margin-left: 5px; transition: .2s; }
.text-link:hover i { transform: translateX(5px); }
.services-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 22px; }
.service-card {
    background: white; border: 1px solid #e5edf6; border-radius: 15px;
    overflow: hidden; box-shadow: 0 7px 22px #0a31510b; transition: .3s;
}
.service-card:hover { transform: translateY(-7px); box-shadow: 0 16px 35px #0a31511c; }
.service-image { height: 190px; overflow: hidden; position: relative; }
.service-image img { width: 100%; height: 100%; object-fit: cover; transition: .5s; }
.service-card:hover .service-image img { transform: scale(1.07); }
.service-badge {
    position: absolute; bottom: -18px; left: 18px; width: 42px; height: 42px;
    display: grid; place-items: center; background: var(--orange);
    border: 4px solid white; color: white; border-radius: 50%; font-size: 15px;
}
.service-body { padding: 25px 19px 21px; }
.service-body h3 { color: var(--navy); font-size: 16px; margin-bottom: 9px; }
.service-body p { color: #74849a; font-size: 12px; line-height: 1.7; min-height: 42px; margin-bottom: 16px; }
.service-body a { color: #ed790e; font-size: 12px; font-weight: 800; }

/* HOW IT WORKS */
.how-section { background: linear-gradient(120deg, #062343, #0b4270); color: white; }
.how-section .section-heading h2 { color: white; }
.how-section .section-heading p { color: #c2d5e8; }
.steps { display: grid; grid-template-columns: repeat(3,1fr); gap: 25px; }
.step {
    display: flex; gap: 18px; align-items: flex-start;
    padding: 24px; border: 1px solid #ffffff20; background: #ffffff08;
    border-radius: 15px; transition: .25s;
}
.step:hover { background: #ffffff13; transform: translateY(-4px); }
.step-number {
    width: 54px; height: 54px; flex-shrink: 0; display: grid; place-items: center;
    border-radius: 16px; background: var(--orange); color: var(--navy);
    font-size: 20px; font-weight: 800;
}
.step h3 { font-size: 16px; margin: 4px 0 10px; }
.step p { color: #c6d8e9; font-size: 12px; line-height: 1.8; }

/* CTA */
.cta { padding: 60px 0; }
.cta-box {
    background: linear-gradient(115deg, #ff9d38, #ff7a12);
    padding: 38px 45px; border-radius: 20px; display: flex; align-items: center;
    justify-content: space-between; gap: 25px; box-shadow: 0 15px 35px #f58a222b;
}
.cta-box h2 { color: #08294d; font-size: clamp(23px, 3vw, 32px); }
.cta-box p { color: #593717; margin-top: 9px; font-size: 14px; line-height: 1.6; }
.cta-box .btn { background: #08294d; color: white; white-space: nowrap; }
.cta-box .btn:hover { background: #104576; }

/* FOOTER */
footer { background: #031a32; color: #d4e0ee; padding: 45px 0 20px; }
.footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 35px; padding-bottom: 30px; }
footer .logo { margin-bottom: 17px; }
footer .logo small { color: #a9bdd2; }
.footer-about p { color: #a9bdd2; font-size: 12px; line-height: 1.9; max-width: 300px; }
footer h4 { color: white; margin: 7px 0 18px; font-size: 14px; }
footer ul { list-style: none; }
footer li { margin-bottom: 12px; }
footer li a { color: #b8c9dc; font-size: 12px; }
footer li a:hover { color: var(--orange); }
.socials { display: flex; gap: 10px; margin-top: 18px; }
.socials a { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 50%; background: #ffffff12; color: white; }
.socials a:hover { background: var(--orange); color: var(--navy); }
.footer-bottom { border-top: 1px solid #ffffff18; padding-top: 18px; display: flex; justify-content: space-between; gap: 15px; color: #9eb2c8; font-size: 11px; }

/* RESPONSIVE */
@media(max-width:1050px) {
    .nav-links { gap: 0; }
    .nav-links a { padding: 10px; font-size: 12px; }
    .logo { min-width: auto; }
    .services-grid { grid-template-columns: repeat(2,1fr); }
    .trust-item { padding: 0 10px; }
}
@media(max-width:780px) {
    .nav-inner { min-height: 72px; flex-wrap: wrap; padding: 10px 0; }
    .menu-toggle { display: block; }
    .nav-links {
        display: none; width: 100%; order: 3; flex-direction: column;
        align-items: stretch; padding: 10px 0 15px;
    }
    .nav-links.show { display: flex; }
    .nav-links a { border-radius: 8px; padding: 13px; }
    .nav-actions { margin-left: auto; }
    .nav-actions .btn { padding: 10px 13px; font-size: 11px; }
    .nav-actions .btn-outline { display: none; }
    .hero { min-height: 520px; background-position: 60% center; }
    .hero-content { padding: 55px 0; }
    .trust-grid { grid-template-columns: repeat(2,1fr); gap: 20px 0; }
    .trust-item:nth-child(2) { border-right: none; }
    .trust-item:nth-child(3) { padding-left: 0; }
    .steps { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: repeat(2,1fr); }
    .cta-box { padding: 30px 25px; flex-direction: column; align-items: flex-start; }
}
@media(max-width:480px) {
    .container { width: 92%; }
    .logo-icon { width: 43px; height: 43px; font-size: 22px; }
    .logo-name { font-size: 23px; }
    .logo small { font-size: 9px; }
    .nav-actions .btn { padding: 10px; }
    .hero h1 { font-size: 40px; letter-spacing: -1.5px; }
    .hero h2 { font-size: 16px; }
    .hero p { font-size: 13px; }
    .services-grid { grid-template-columns: 1fr; }
    .service-image { height: 210px; }
    .section { padding: 52px 0; }
    .section-heading { align-items: flex-start; flex-direction: column; }
    .trust-item { padding: 0 8px; gap: 9px; }
    .trust-icon { width: 39px; height: 39px; font-size: 16px; }
    .trust-item strong { font-size: 11px; }
    .trust-item small { font-size: 10px; }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 25px 18px; }
    .footer-bottom { flex-direction: column; line-height: 1.7; }
}
</style>
</head>

<body>

<header class="navbar">
    <div class="container nav-inner">
        <a class="logo" href="#home">
            <div class="logo-icon"><i class="fa-solid fa-truck-fast"></i></div>
            <div>
                <div class="logo-name">SG<span>TS</span></div>
                <small>Smart Goods Transport System</small>
            </div>
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="#home" class="active"><i class="fa-solid fa-house"></i> Home</a>
            <a href="#about">About</a>
            <a href="#services">Services <i class="fa-solid fa-chevron-down"></i></a>
            <a href="#how">How It Works</a>
            <a href="#vehicles">Vehicles</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="nav-actions">
            <a class="btn btn-outline" href="login.php"><i class="fa-regular fa-user"></i> Login</a>
            <a class="btn btn-orange" href="register.php"><i class="fa-solid fa-user-plus"></i> Register</a>
        </div>

        <div class="menu-toggle" onclick="toggleMenu()">
            <i class="fa-solid fa-bars"></i>
        </div>
    </div>
</header>

<main>
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <div class="eyebrow"><i class="fa-solid fa-shield-halved"></i> SAFE &nbsp; • &nbsp; FAST &nbsp; • &nbsp; RELIABLE</div>
            <h1>Smart Goods<br><span>Transport System</span></h1>
            <h2>Move Your Goods, Your Way — With SGTS</h2>
            <p>
                Book a suitable vehicle for home shifting, shop relocation
                and goods transportation. Simple booking, transparent
                estimated charges and dependable service.
            </p>
            <div class="hero-buttons">
                <a class="btn btn-orange" href="register.php"><i class="fa-regular fa-calendar-check"></i> Book Your Transport <i class="fa-solid fa-arrow-right"></i></a>
                <a class="btn btn-outline" href="#services">Explore Services</a>
            </div>
            <div class="hero-note">
                <span><i class="fa-solid fa-circle-check"></i> Easy Booking</span>
                <span><i class="fa-solid fa-truck"></i> Multiple Vehicles</span>
                <span><i class="fa-solid fa-box"></i> Packing Option</span>
            </div>
        </div>
    </div>
</section>

<section class="trust-bar" id="about">
    <div class="container trust-grid">
        <div class="trust-item">
            <div class="trust-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div><strong>Safe Handling</strong><small>Care for your goods</small></div>
        </div>
        <div class="trust-item">
            <div class="trust-icon"><i class="fa-solid fa-clock"></i></div>
            <div><strong>Easy Scheduling</strong><small>Choose a suitable date</small></div>
        </div>
        <div class="trust-item">
            <div class="trust-icon"><i class="fa-solid fa-headset"></i></div>
            <div><strong>Booking Support</strong><small>Clear booking details</small></div>
        </div>
        <div class="trust-item">
            <div class="trust-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div><strong>Track Your Booking</strong><small>Check status updates</small></div>
        </div>
    </div>
</section>

<section class="section" id="services">
    <div class="container">
        <div class="section-heading">
            <div>
                <div class="kicker">OUR SERVICES</div>
                <h2>Transport Solutions for Every Need</h2>
                <p>Choose a service that fits your goods and moving requirements.</p>
            </div>
            <a class="text-link" href="register.php">Get Started <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="services-grid">
            <article class="service-card">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80" alt="Home relocation">
                    <div class="service-badge"><i class="fa-solid fa-house"></i></div>
                </div>
                <div class="service-body">
                    <h3>Home Shifting</h3>
                    <p>Move furniture, appliances, boxes and household items to your new home.</p>
                    <a href="register.php">Book Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="service-card">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1604719312566-8912e9c8a213?auto=format&fit=crop&w=800&q=80" alt="Shop goods and inventory">
                    <div class="service-badge"><i class="fa-solid fa-shop"></i></div>
                </div>
                <div class="service-body">
                    <h3>Shop Shifting</h3>
                    <p>Transport shop inventory, business equipment and commercial goods.</p>
                    <a href="register.php">Book Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="service-card">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80" alt="Goods transport warehouse">
                    <div class="service-badge"><i class="fa-solid fa-truck"></i></div>
                </div>
                <div class="service-body">
                    <h3>Goods Transport</h3>
                    <p>Book a suitable vehicle to move your goods between locations.</p>
                    <a href="register.php">Book Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </article>

            <article class="service-card" id="vehicles">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=800&q=80" alt="Transport truck">
                    <div class="service-badge"><i class="fa-solid fa-truck-fast"></i></div>
                </div>
                <div class="service-body">
                    <h3>Vehicle Options</h3>
                    <p>Choose a vehicle according to your load, distance and budget.</p>
                    <a href="register.php">Choose Vehicle <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section how-section" id="how">
    <div class="container">
        <div class="section-heading">
            <div>
                <div class="kicker">HOW IT WORKS</div>
                <h2>Move in 3 Simple Steps</h2>
                <p>A straightforward process for your transport booking.</p>
            </div>
        </div>

        <div class="steps">
            <div class="step">
                <div class="step-number">01</div>
                <div><h3>Enter Your Details</h3><p>Add pickup address, delivery address, moving date and details of your goods.</p></div>
            </div>
            <div class="step">
                <div class="step-number">02</div>
                <div><h3>Choose a Vehicle</h3><p>Select a suitable vehicle and packing option, then review the estimated fare.</p></div>
            </div>
            <div class="step">
                <div class="step-number">03</div>
                <div><h3>Confirm Your Booking</h3><p>Submit your request and check booking status from your account.</p></div>
            </div>
        </div>
    </div>
</section>

<section class="cta" id="contact">
    <div class="container">
        <div class="cta-box">
            <div>
                <h2>Planning to Move Your Goods?</h2>
                <p>Create an account to start your transport booking with SGTS.</p>
            </div>
            <a href="register.php" class="btn"><i class="fa-solid fa-arrow-right"></i> Start Booking</a>
        </div>
    </div>
</section>
</main>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-about">
                <a class="logo" href="#home">
                    <div class="logo-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <div><div class="logo-name">SG<span>TS</span></div><small>Smart Goods Transport System</small></div>
                </a>
                <p>A web-based goods transport booking project designed to make local shifting and goods movement simpler.</p>
                <div class="socials">
                    <a href="#contact" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#contact" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#contact" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>

            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About SGTS</a></li>
                    <li><a href="#services">Our Services</a></li>
                    <li><a href="#how">How It Works</a></li>
                </ul>
            </div>

            <div>
                <h4>Our Services</h4>
                <ul>
                    <li><a href="#services">Home Shifting</a></li>
                    <li><a href="#services">Shop Shifting</a></li>
                    <li><a href="#services">Goods Transport</a></li>
                    <li><a href="#vehicles">Vehicle Options</a></li>
                </ul>
            </div>

            <div>
                <h4>Account</h4>
                <ul>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Create Account</a></li>
                    <li><a href="login.php">My Bookings</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 SGTS — Smart Goods Transport System</span>
            <span>MCA Final Year Project</span>
        </div>
    </div>
</footer>

<script>
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('show');
}
</script>

</body>
</html>
