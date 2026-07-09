

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

Alpine.start();

// Render grafik dari konfigurasi JSON di atribut data-chart (halaman analitik).
document.querySelectorAll('canvas[data-chart]').forEach((el) => {
    new Chart(el, JSON.parse(el.dataset.chart));
});
