.report-header { text-align: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #dee2e6; }
.report-title { font-size: 1.5rem; font-weight: 600; margin-top: 0.5rem; }
.firmas-block { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #dee2e6; }
.firma-item { display: inline-block; text-align: center; margin: 1rem 2rem 1rem 0; min-width: 180px; }
.firma-line { border-bottom: 1px solid #333; width: 100%; height: 2.5rem; margin-bottom: 0.25rem; }
.firma-nombre { font-weight: 600; font-size: 0.9rem; }
.firma-cargo { font-size: 0.8rem; color: #6c757d; }
@media print {
    .report-header, .report-title, .table, .firmas-block { break-inside: avoid; }
}
@page { margin: 0.5in; }
