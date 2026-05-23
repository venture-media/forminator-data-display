/**
 * Forminator Data Display - Highlight highest count
 */
(function () {
    'use strict';

    function highlightHighestInTable(table) {
        // Prevent running twice on the same table
        if (table.classList.contains('ffd-highlighted')) return;
        table.classList.add('ffd-highlighted');

        const rows = table.querySelectorAll('tbody tr');
        let maxCount = -1;
        let maxRow = null;

        rows.forEach(function (row) {
            const tds = row.querySelectorAll('td');
            if (tds.length < 2) return;

            // Skip the "Total" row
            if (tds[0].textContent.trim() === 'Total') return;

            const countText = tds[1].textContent.trim();
            const count = parseInt(countText, 10);

            if (!isNaN(count) && count > maxCount) {
                maxCount = count;
                maxRow = row;
            }
        });

        // Inline !important background to every <td> in the winning row
        if (maxRow) {
            const cells = maxRow.querySelectorAll('td');
            cells.forEach(function (cell) {
                cell.style.setProperty('background-color', '#d4edda', 'important');
            });
        }
    }

    function highlightAllTables() {
        const tables = document.querySelectorAll('table.ffd-table');
        tables.forEach(highlightHighestInTable);
    }

    // Run on page load
    document.addEventListener('DOMContentLoaded', highlightAllTables);
    window.addEventListener('load', highlightAllTables);

    // Watch for Elementor accordions opening
    const observer = new MutationObserver(highlightAllTables);
    observer.observe(document.body, { childList: true, subtree: true });

    // Extra safety for accordion clicks
    document.addEventListener('click', function (e) {
        if (e.target.closest('.elementor-accordion-item') || e.target.closest('.elementor-tab-title')) {
            setTimeout(highlightAllTables, 150);
        }
    });
})();
