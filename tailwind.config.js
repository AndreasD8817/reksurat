/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./pages/**/*.php", // Semua file .php di dalam folder pages
    "./templates/**/*.php", // Semua file .php di dalam folder templates
    "./assets/js/**/*.js", // Semua file .js di dalam folder assets/js
    "./index.php", // File index.php di root
  ],
  theme: {
    extend: {
      colors: {
        primary: "#4f46e5",
        secondary: "#6366f1",
        dark: "#1f2937",
      },
      animation: {
        "fade-in": "fadeIn 0.5s ease-out",
        "slide-in": "slideIn 0.3s ease-out",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" },
        },
        slideIn: {
          "0%": { transform: "translateX(-100%)" },
          "100%": { transform: "translateX(0)" },
        },
      },
    },
  },
  plugins: [],
};
