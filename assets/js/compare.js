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
                <div class="table-entry">
                    <div class="form-check d-flex align-items-center">
                        <input type="checkbox" class="form-check-input" name="tables${dbSelect.id.slice(-1)}[]" 
                               value="${table}" id="${dbSelect.id}-${table}" checked>
                        <label class="form-check-label" for="${dbSelect.id}-${table}">${table}</label>
                    </div>
                    <div class="differences" id="${dbSelect.id}-${table}-diff"></div>
                </div>
            `).join('');
            highlightMissingTables();
        });
}

function formatDifference(type, fields, dbContext = '') {
    if (!fields || !fields.length) return '';
    return `<div class="difference-item">
        <span class="keyword">${type}</span>
        <span class="field-list">${Array.isArray(fields) ? fields.join(', ') : fields}</span>
        ${dbContext ? `<span class="db-context">${dbContext}</span>` : ''}
    </div>`;
}

function formatTypeMismatch(typeMismatch, dbId) {
    if (!typeMismatch.length) return '';
    
    return `<div class="type-mismatch">
        <span class="difference-label">Type mismatch:</span>
        ${typeMismatch.map(mismatch => {
            // Each mismatch should contain: "field_name: type1 vs type2"
            const [fieldName, typeInfo] = mismatch.split(': ');
            const [db1Type, db2Type] = typeInfo.split(' vs ');
            return `<div class="field-list">
                ${fieldName}: ${dbId === 'db1' ? db1Type : db2Type} vs ${dbId === 'db1' ? db2Type : db1Type}
            </div>`;
        }).join('')}
    </div>`;
}

function highlightMissingTables() {
    const db1 = document.getElementById('db1').value;
    const db2 = document.getElementById('db2').value;

    if (!db1 || !db2) return;

    // Reset all differences
    document.querySelectorAll('.differences').forEach(div => div.innerHTML = '');

    fetch(`ajax/compare_tables.php?db1=${db1}&db2=${db2}`)
        .then(handleFetchError)
        .then(response => response.json())
        .then(data => {
            if (!data || typeof data !== 'object') {
                console.error('Invalid data received from server');
                return;
            }

            // Reset all labels
            document.querySelectorAll('#tables1 label, #tables2 label').forEach(label => {
                label.classList.remove('text-danger');
                label.setAttribute('title', '');
            });
            
            // Handle missing tables
            if (Array.isArray(data.db1)) {
                data.db1.forEach(table => {
                    const checkbox = document.querySelector(`#db1-${table}`);
                    const label = checkbox?.nextElementSibling;
                    if (checkbox && label) {
                        label.classList.add('text-danger');
                        checkbox.checked = false;
                        label.setAttribute('title', 'Table missing in Database 2');
                    }
                });
            }
            
            if (Array.isArray(data.db2)) {
                data.db2.forEach(table => {
                    const checkbox = document.querySelector(`#db2-${table}`);
                    const label = checkbox?.nextElementSibling;
                    if (checkbox && label) {
                        label.classList.add('text-danger');
                        checkbox.checked = false;
                        label.setAttribute('title', 'Table missing in Database 1');
                    }
                });
            }

            // Display differences under each table
            if (data.differences && typeof data.differences === 'object') {
                const allTables = new Set([
                    ...document.querySelectorAll('#tables1 .table-entry label'),
                    ...document.querySelectorAll('#tables2 .table-entry label')
                ].map(label => label.textContent));

                allTables.forEach(table => {
                    const diffs = data.differences[table] || [];
                    ['db1', 'db2'].forEach(dbId => {
                        const diffDiv = document.getElementById(`${dbId}-${table}-diff`);
                        if (!diffDiv) return;

                        // If table exists in both DBs and has no differences
                        if (!diffs.length && 
                            !data.db1.includes(table) && 
                            !data.db2.includes(table)) {
                            diffDiv.innerHTML = '<div class="identical-notice">✓ Identical structure</div>';
                            return;
                        }

                        const missingInDb1 = diffs
                            .filter(d => d[0]?.startsWith('Field missing in db1'))
                            .map(d => {
                                const field = d[0]?.split(': ')[1];
                                return field || '';
                            })
                            .filter(field => field !== '');

                        const missingInDb2 = diffs
                            .filter(d => d[0]?.startsWith('Field missing in db2'))
                            .map(d => {
                                const field = d[0]?.split(': ')[1];
                                return field || '';
                            })
                            .filter(field => field !== '');

                        const typeMismatch = diffs
                            .filter(d => d[0]?.includes('type mismatch'))
                            .map(d => {
                                // Split "Field field_name type mismatch: type1 vs type2"
                                const [prefix, typeInfo] = d[0].split('type mismatch: ');
                                const fieldName = prefix.split(' ')[1]; // Get the field name
                                return `${fieldName}: ${typeInfo}`; // Return "field_name: type1 vs type2"
                            });

                        let html = '';
                        if (missingInDb2.length) {
                            html += formatDifference('Missing', missingInDb2, 'in DB2');
                        }
                        if (missingInDb1.length) {
                            html += formatDifference('Missing', missingInDb1, 'in DB1');
                        }
                        if (typeMismatch.length) {
                            html += formatTypeMismatch(typeMismatch, dbId);
                        }
                        diffDiv.innerHTML = html;
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error fetching comparison data:', error);
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
