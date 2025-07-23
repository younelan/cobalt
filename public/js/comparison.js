document.addEventListener('DOMContentLoaded', function() {
    function qs(sel, ctx=document) { return ctx.querySelector(sel); }
    function qsa(sel, ctx=document) { return Array.from(ctx.querySelectorAll(sel)); }

    function setHTML(el, html) { el.innerHTML = html; }
    function setVal(el, val) { el.value = val; }
    function getVal(el) { return el.value; }
    function setProp(el, prop, val) { el[prop] = val; }
    function closest(el, sel) { while (el && !el.matches(sel)) el = el.parentElement; return el; }

    const db1 = qs('#db1');
    const db2 = qs('#db2');
    const results = qs('#comparison-results');

    function loadComparison() {
        const db1Val = getVal(db1);
        const db2Val = getVal(db2);
        if (db1Val && db2Val) {
            setHTML(results, '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Loading comparison...</div></div>');
            const formData = new URLSearchParams({ db1: db1Val, db2: db2Val });
            fetch('ajax/compare_tables.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(r => r.text())
            .then(response => {
                try {
                    const jsonResponse = JSON.parse(response);
                    if (jsonResponse.error) {
                        setHTML(results, '<div class="alert alert-danger">' + jsonResponse.error + '</div>');
                        return;
                    }
                } catch(e) {
                    setHTML(results, response);
                    initializeComparison();
                }
            })
            .catch(error => {
                setHTML(results, '<div class="alert alert-danger">Error: ' + error + '</div>');
            });
            localStorage.setItem('selectedDb1', db1Val);
            localStorage.setItem('selectedDb2', db2Val);
        } else {
            setHTML(results, '<div class="alert alert-info">Please select two databases to compare.</div>');
        }
    }

    // Load stored selections
    const storedDb1 = localStorage.getItem('selectedDb1');
    const storedDb2 = localStorage.getItem('selectedDb2');
    if (storedDb1) setVal(db1, storedDb1);
    if (storedDb2) setVal(db2, storedDb2);

    function initializeComparison() {
        // Remove all existing event handlers and rebind (event delegation)
        document.removeEventListener('change', delegatedChangeHandler, true);
        document.removeEventListener('click', delegatedClickHandler, true);
        document.addEventListener('change', delegatedChangeHandler, true);
        document.addEventListener('click', delegatedClickHandler, true);
    }

    function delegatedChangeHandler(e) {
        if (e.target.matches('#select-all')) {
            qsa('.table-select').forEach(cb => setProp(cb, 'checked', e.target.checked));
        }
        if (e.target.matches('.compare-with')) {
            const tableRow = closest(e.target, '.table-entry');
            const originalTable = tableRow.dataset.table;
            const compareWith = getVal(e.target);
            if (!compareWith) return;
            const formData = new URLSearchParams({
                db1: getVal(db1),
                db2: getVal(db2),
                table1: originalTable,
                table2: compareWith,
                selected: compareWith
            });
            fetch('ajax/compare_single_table.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                if (result.error) {
                    alert(result.error);
                    return;
                }
                const temp = document.createElement('div');
                temp.innerHTML = result.html;
                const newRow = temp.firstElementChild;
                tableRow.parentNode.replaceChild(newRow, tableRow);
                setVal(qs('.compare-with', newRow), compareWith);
            })
            .catch(e => { alert('Error updating comparison'); });
        }
    }

    function delegatedClickHandler(e) {
        if (e.target.matches('.compare-details')) {
            const tableRow = closest(e.target, '.table-entry');
            const table = tableRow.id.replace('table-row-', '');
            const compareWith = qs('.compare-with', tableRow).value;
            alert('Details for ' + table + ' compared with ' + compareWith);
        }
        if (e.target.matches('.view-summary, .view-details')) {
            e.preventDefault();
            const tableRow = closest(e.target, '.table-entry');
            const table1 = tableRow.dataset.table;
            const table2 = qs('.compare-with', tableRow).value;
            const viewType = e.target.classList.contains('view-summary') ? 'summary' : 'details';
            if (viewType === 'summary') {
                const formData = new URLSearchParams({
                    db1: getVal(db1),
                    db2: getVal(db2),
                    table1: table1,
                    table2: table2,
                    selected: table2,
                    force_fresh: true,
                    view: 'summary'
                });
                fetch('ajax/compare_single_table.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                .then(r => r.json())
                .then(result => {
                    if (result.html) {
                        const temp = document.createElement('div');
                        temp.innerHTML = result.html;
                        const newRow = temp.firstElementChild;
                        tableRow.parentNode.replaceChild(newRow, tableRow);
                    }
                });
            } else {
                const formData = new URLSearchParams({
                    db1: getVal(db1),
                    db2: getVal(db2),
                    table1: table1,
                    table2: table2,
                    selected: table2
                });
                fetch('ajax/get_table_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                })
                .then(r => r.json())
                .then(result => {
                    if (result.html) {
                        const temp = document.createElement('div');
                        temp.innerHTML = result.html;
                        const newRow = temp.firstElementChild;
                        tableRow.parentNode.replaceChild(newRow, tableRow);
                    }
                });
            }
            // Update dropdown active state
            qsa('.dropdown-item', tableRow).forEach(item => item.classList.remove('active'));
            if (viewType === 'summary') {
                qsa('.view-summary', tableRow).forEach(item => item.classList.add('active'));
            } else {
                qsa('.view-details', tableRow).forEach(item => item.classList.add('active'));
            }
        }
    }

    db1.addEventListener('change', loadComparison);
    db2.addEventListener('change', loadComparison);
    if (getVal(db1) && getVal(db2)) {
        loadComparison();
    }
});
