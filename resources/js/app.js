// Alpine.js NO se importa aquí: Livewire ya lo incluye y cargarlo dos veces rompe los componentes.
import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;

// Gráficas del dashboard: solo se cargan si su contenedor existe en la página
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
});
