<?php
// Mengirim header 503 Service Unavailable, ini baik untuk SEO
http_response_code(503);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ArekSurat - Sedang Dalam Perbaikan</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;700;900&display=swap');
        
        :root {
            --primary: #4f46e5; /* Warna utama dari proyek Anda */
            --secondary: #6366f1; /* Warna sekunder dari proyek Anda */
            --accent: #a5b4fc; /* Aksen yang serasi */
            --dark: #2D3436;
            --light: #F9F9F9;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            padding: 20px;
            position: relative;
        }
        
        .maintenance-container {
            text-align: center;
            padding: 1.5rem;
            max-width: 800px;
            width: 100%;
            position: relative;
            z-index: 10;
        }
        
        h1 {
            font-size: 2.8rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--accent), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.2;
        }
        
        p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .tools-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .tool {
            font-size: 4rem;
            animation: bounce 2s infinite ease-in-out;
        }
        
        .tool-screwdriver {
            animation-delay: 0.2s;
        }
        
        .tool-wrench {
            animation-delay: 0.4s;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(10deg); }
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 1.5rem 0;
        }
        
        .countdown-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 0.8rem;
            border-radius: 8px;
            min-width: 70px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .countdown-number {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .countdown-label {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        
        .progress-container {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            margin: 1.5rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            width: 65%;
            background: linear-gradient(to right, var(--accent), var(--secondary));
            border-radius: 4px;
            animation: progress-animation 2s ease-in-out infinite alternate;
        }
        
        @keyframes progress-animation {
            0% { width: 65%; }
            100% { width: 70%; }
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            margin-top: 1.5rem;
        }
        
        .social-link {
            color: var(--light);
            background: rgba(255, 255, 255, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }
        
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.2;
            filter: blur(40px);
        }
        
        .shape-1 {
            width: 200px;
            height: 200px;
            background: var(--accent);
            top: -50px;
            left: -50px;
            animation: float 15s infinite ease-in-out;
        }
        
        .shape-2 {
            width: 250px;
            height: 250px;
            background: var(--secondary);
            bottom: -80px;
            right: -50px;
            animation: float 18s infinite ease-in-out reverse;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: var(--light);
            top: 50%;
            left: 30%;
            animation: float 12s infinite ease-in-out;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, 30px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        
        .copyright {
            margin-top: 2rem;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .social-link svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
        }
        
        /* Animasi gambar jatuh */
        .falling-image {
            position: absolute;
            top: -100px;
            z-index: 2;
            animation: fall linear infinite;
            opacity: 0.7;
            pointer-events: none;
            user-select: none;
        }

        @keyframes fall {
            to {
                transform: translateY(110vh) rotate(360deg);
            }
        }

        /* Variasi ukuran untuk gambar */
        .size-small {
            width: 40px;
            height: 40px;
            animation-duration: 15s;
        }

        .size-medium {
            width: 70px;
            height: 70px;
            animation-duration: 20s;
        }

        .size-large {
            width: 100px;
            height: 100px;
            animation-duration: 25s;
        }

        /* Variasi delay untuk animasi */
        .delay-1 {
            animation-delay: 0s;
        }

        .delay-2 {
            animation-delay: 3s;
        }

        .delay-3 {
            animation-delay: 6s;
        }

        .delay-4 {
            animation-delay: 9s;
        }
        
        /* Media queries untuk perangkat mobile */
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .maintenance-container {
                padding: 1rem;
            }
            
            h1 {
                font-size: 2.2rem;
            }
            
            p {
                font-size: 0.9rem;
            }
            
            .tools-container {
                gap: 1.5rem;
            }
            
            .tool {
                font-size: 3.5rem;
            }
            
            .countdown {
                gap: 0.5rem;
            }
            
            .countdown-item {
                min-width: 60px;
                padding: 0.6rem;
            }
            
            .countdown-number {
                font-size: 1.5rem;
            }
            
            .countdown-label {
                font-size: 0.7rem;
            }
            
            .shape-1,
            .shape-2,
            .shape-3 {
                transform: scale(0.8);
            }
            
            /* Ukuran gambar lebih kecil di mobile */
            .size-small {
                width: 30px;
                height: 30px;
            }

            .size-medium {
                width: 50px;
                height: 50px;
            }

            .size-large {
                width: 70px;
                height: 70px;
            }
        }
        
        @media (max-width: 350px) {
            h1 {
                font-size: 1.8rem;
            }
            
            .tools-container {
                gap: 1rem;
            }
            
            .tool {
                font-size: 3rem;
            }
            
            .countdown-item {
                min-width: 50px;
                padding: 0.5rem;
            }
            
            .countdown-number {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <div class="maintenance-container">
        <div class="tools-container">
            <div class="tool tool-screwdriver">🛠️</div>
            <div class="tool tool-wrench">🔧</div>
        </div>
        <h1>Sedang Dalam Perbaikan!</h1>
        <p>Kami sedang melakukan upgrade sistem untuk memberikan pengalaman yang lebih baik. Situs akan kembali online segera.</p>
        
        <div class="countdown">
            <div class="countdown-item">
                <div class="countdown-number" id="days">05</div>
                <div class="countdown-label">Hari</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="hours">12</div>
                <div class="countdown-label">Jam</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="minutes">45</div>
                <div class="countdown-label">Menit</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="seconds">30</div>
                <div class="countdown-label">Detik</div>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
        <p>Kami memohon maaf atas ketidaknyamanan ini. Silakan ikuti media sosial kami untuk update terbaru.</p>
        
        <div class="social-links">
            <a href="#" class="social-link" target="_blank" title="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
            </a>
            <a href="#" class="social-link" target="_blank" title="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </a>
            <a href="#" class="social-link" target="_blank" title="Twitter">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                </svg>
            </a>
            <a href="#" class="social-link" target="_blank" title="Website">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
            </a>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> ArekSurat. Hak Cipta Dilindungi.</p>
        </div>
    </div>
    
    <script>
    // Fungsi untuk update countdown setiap detik
    function updateCountdown() {
        // Waktu saat ini
        const now = new Date();
        
        // TARGET WAKTU SELESAI MAINTENANCE (SILAHKAN EDIT BAGIAN INI)
        // Format: Tahun, Bulan (0-11), Tanggal, Jam, Menit
        // Contoh: 31 Desember 2024 pukul 23:00
        const target = new Date(2025, 11, 31, 23, 0); 
        // Catatan: Bulan dimulai dari 0 (0=Januari, 11=Desember)
        // ----------------------------------------------------------
        
        // Hitung selisih waktu antara sekarang dan target
        const diff = target - now;
        
        // Jika waktu maintenance sudah lewat
        if (diff <= 0) {
            document.getElementById('days').textContent = '00';
            document.getElementById('hours').textContent = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
            return;
        }
        
        // Hitung hari, jam, menit, detik dari selisih waktu
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Update tampilan countdown di halaman
        document.getElementById('days').textContent = days.toString().padStart(2, '0');
        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    }
    
    // Jalankan fungsi updateCountdown setiap 1 detik
    setInterval(updateCountdown, 1000);
    
    // Jalankan sekali saat pertama kali halaman dimuat
    updateCountdown();
    
    // Fungsi untuk membuat gambar jatuh
    document.addEventListener('DOMContentLoaded', function() {
        // Jumlah gambar yang akan dijatuhkan
        const imageCount = 15;
        const container = document.body;
        
        // Buat elemen gambar yang jatuh
        for (let i = 0; i < imageCount; i++) {
            const fallingImg = document.createElement('img');
            fallingImg.src = '../assets/img/wawan.png';
            fallingImg.classList.add('falling-image');
            fallingImg.alt = 'Wawan';
            
            // Tentukan ukuran acak
            const sizes = ['size-small', 'size-medium', 'size-large'];
            const randomSize = sizes[Math.floor(Math.random() * sizes.length)];
            fallingImg.classList.add(randomSize);
            
            // Tentukan delay acak
            const delays = ['delay-1', 'delay-2', 'delay-3', 'delay-4'];
            const randomDelay = delays[Math.floor(Math.random() * delays.length)];
            fallingImg.classList.add(randomDelay);
            
            // Posisi horizontal acak
            const randomLeft = Math.floor(Math.random() * 100);
            fallingImg.style.left = randomLeft + 'vw';
            
            // Rotasi awal acak
            const randomRotation = Math.floor(Math.random() * 360);
            fallingImg.style.transform = `rotate(${randomRotation}deg)`;
            
            container.appendChild(fallingImg);
        }
    });
    </script>
</body>
</html>