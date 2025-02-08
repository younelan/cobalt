function handleAuth() {
    const form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Authentication failed');
                }
            });
        });
    }
}

function handleFetchError(response) {
    if (!response.ok) {
        if (response.status === 401) {
            window.location.reload(); // Redirect to login
        }
        throw new Error('Network response was not ok');
    }
    return response;
}

function updateTables(dbSelect, tableContainer) {
    const dbName = dbSelect.value;
    if (!dbName) {
        tableContainer.innerHTML = '';
        return;
    }

    fetch(`ajax/get_tables.php?db=${dbName}`)
        .then(handleFetchError)
        .then(response => response.json())
        .then(tables => {
            tableContainer.innerHTML = tables.map(table => `
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="tables${dbSelect.id.slice(-1)}[]" 
                           value="${table}" id="${dbSelect.id}-${table}" checked>
                    <label class="form-check-label" for="${dbSelect.id}-${table}">${table}</label>
                </div>
            `).join('');
            highlightMissingTables();
        });
}

function highlightMissingTables() {
    const db1 = document.getElementById('db1').value;
    const db2 = document.getElementById('db2').value;

    if (!db1 || !db2) return;

    fetch(`ajax/compare_tables.php?db1=${db1}&db2=${db2}`)
        .then(handleFetchError)
        .then(response => response.json())
        .then(data => {
            // Reset all labels and checkboxes
            document.querySelectorAll('#tables1 label, #tables2 label').forEach(label => {
                label.classList.remove('text-danger');
                label.setAttribute('title', '');
            });
            
            // Handle missing tables
            data.db1.forEach(table => {
                const checkbox = document.querySelector(`#db1-${table}`);
                const label = checkbox?.nextElementSibling;
                if (checkbox && label) {
                    label.classList.add('text-danger');
                    checkbox.checked = false;
                    label.setAttribute('title', 'Table missing in Database 2');
                }
            });
            
            data.db2.forEach(table => {
                const checkbox = document.querySelector(`#db2-${table}`);
                const label = checkbox?.nextElementSibling;
                if (checkbox && label) {
                    label.classList.add('text-danger');
                    checkbox.checked = false;
                    label.setAttribute('title', 'Table missing in Database 1');
                }
            });

            // Display differences in the comparison results area
            const resultsDiv = document.getElementById('comparison-results');
            if (Object.keys(data.differences).length > 0) {
                let html = '<div class="alert alert-warning"><h4>Structure Differences Found:</h4><ul>';
                for (const [table, diffs] of Object.entries(data.differences)) {
                    html += `<li><strong>${table}</strong><ul>`;
                    diffs.forEach(diff => {
                        html += `<li>${diff[0]}</li>`;
                    });
                    html += '</ul></li>';
                }
                html += '</ul></div>';
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = '<div class="alert alert-success">No structure differences found in common tables.</div>';
            }
        });
}

function handleSelectAll(target, select) {
    document.querySelectorAll(`#tables${target} input[type="checkbox"]`).forEach(checkbox => {
        if (!checkbox.closest('label')?.classList.contains('text-danger')) {
            checkbox.checked = select;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    handleAuth();
    // Initialize from localStorage
    ['db1', 'db2'].forEach(dbId => {
        const storedDb = localStorage.getItem(dbId);
        const select = document.getElementById(dbId);
        if (storedDb) {
            select.value = storedDb;
            updateTables(select, document.getElementById(`tables${dbId.slice(-1)}`));
        }
    });

    // Event Listeners for database selection
    document.querySelectorAll('#db1, #db2').forEach(select => {
        select.addEventListener('change', (e) => {
            localStorage.setItem(e.target.id, e.target.value);
            updateTables(e.target, document.getElementById(`tables${e.target.id.slice(-1)}`));
        });
    });

    // Select All/None buttons
    document.querySelectorAll('.select-all-btn, .select-none-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const target = e.target.dataset.target;
            handleSelectAll(target, e.target.classList.contains('select-all-btn'));
        });
    });

    // Form validation
    document.getElementById('dbCompareForm').addEventListener('submit', (e) => {
        const db1 = document.getElementById('db1').value;
        const db2 = document.getElementById('db2').value;
        
        if (!db1 || !db2) {
            e.preventDefault();
            alert('Please select both databases before comparing.');
            return;
        }

        const tables1 = document.querySelectorAll('#tables1 input:checked');
        const tables2 = document.querySelectorAll('#tables2 input:checked');
        
        if (!tables1.length || !tables2.length) {
            e.preventDefault();
            alert('Please select at least one table from each database.');
            return;
        }
        
        // Form is valid, let it submit normally
    });
});
