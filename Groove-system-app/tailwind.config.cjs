/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        planet: ['PlanetKosmos', 'sans-serif'],
        progress: ['Progress', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
