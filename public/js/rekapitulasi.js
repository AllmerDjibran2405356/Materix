// rekapitulasi.js
export class RekapitulasiExporter {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupPrintButton();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            // Setup tooltips
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
    }

    setupPrintButton() {
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-light ms-2';
        printBtn.innerHTML = '<i class="bi bi-printer"></i> Print';
        printBtn.onclick = () => window.print();

        const exportButtons = document.querySelector('.export-buttons');
        if (exportButtons) {
            exportButtons.appendChild(printBtn);
        }
    }

    showLoading(message) {
        const overlay = document.getElementById('loadingOverlay');
        const text = document.getElementById('loadingText');
        if (overlay && text) {
            text.textContent = message;
            overlay.classList.add('active');
        }
    }

    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
}

// Initialize
if (typeof window !== 'undefined') {
    window.rekapitulasiExporter = new RekapitulasiExporter();
}
