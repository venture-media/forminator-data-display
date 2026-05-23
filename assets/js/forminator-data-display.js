/**
 * Forminator Data Display - Highlight highest count
 */
(function () {
    'use strict';

    function getCleanCount(td) {
        if (!td) return 0;
        // Remove ALL non-numeric characters
        const text = td.textContent.replace(/\D/g, '').trim();
        return parseInt(text, 10) || 0;
    }

    function highlightHighestInTable(table) {
        const rows = table.querySelectorAll('tbody tr');
        let maxCount = -1;

        // First pass: find the true maximum count
        rows.forEach(function (row) {
            const tds = row.querySelectorAll('td');
            if (tds.length < 2) return;

            // Skip the "Total" row
            if (tds[0].textContent.trim() === 'Total') return;

            const count = getCleanCount(tds[1]);
            if (count > maxCount) {
                maxCount = count;
            }
        });

        // Second pass: highlight every row that matches the maximum count
        if (maxCount > -1) {
            rows.forEach(function (row) {
                const tds = row.querySelectorAll('td');
                if (tds.length < 2) return;
                if (tds[0].textContent.trim() === 'Total') return;

                const count = getCleanCount(tds[1]);

                if (count === maxCount) {
                    const cells = row.querySelectorAll('td');
                    cells.forEach(function (cell) {
                        cell.style.setProperty('background-color', '#d4edda', 'important');
                    });
                }
            });
        }
    }

    function highlightAllTables() {
        document.querySelectorAll('table.ffd-table').forEach(highlightHighestInTable);
    }

    // Run on page load
    document.addEventListener('DOMContentLoaded', highlightAllTables);
    window.addEventListener('load', highlightAllTables);

    // Watch for Elementor accordions / dynamic content
    const observer = new MutationObserver(highlightAllTables);
    observer.observe(document.body, { childList: true, subtree: true });

    // Extra trigger when accordions open
    document.addEventListener('click', function (e) {
        if (e.target.closest('.elementor-accordion-item') || e.target.closest('.elementor-tab-title')) {
            setTimeout(highlightAllTables, 300);
        }
    });
})();
