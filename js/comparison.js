$(document).ready(function() {
    function loadComparison() {
        const db1 = $('#db1').val();
        const db2 = $('#db2').val();
        
        if (db1 && db2) {
            $('#comparison-results').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Loading comparison...</div></div>');
            
            $.ajax({
                url: 'ajax/compare_tables.php',
                method: 'POST',
                data: { db1: db1, db2: db2 },
                success: function(response) {
                    try {
                        // Check if response is JSON error
                        const jsonResponse = JSON.parse(response);
                        if (jsonResponse.error) {
                            $('#comparison-results').html('<div class="alert alert-danger">' + jsonResponse.error + '</div>');
                            return;
                        }
                    } catch(e) {
                        // Not JSON, treat as HTML
                        $('#comparison-results').html(response);
                        initializeComparison();
                    }
                },
                error: function(xhr, status, error) {
                    $('#comparison-results').html('<div class="alert alert-danger">Error: ' + error + '</div>');
                }
            });

            // Store selected databases
            localStorage.setItem('selectedDb1', db1);
            localStorage.setItem('selectedDb2', db2);
        } else {
            $('#comparison-results').html('<div class="alert alert-info">Please select two databases to compare.</div>');
        }
    }

    // Load stored selections
    const storedDb1 = localStorage.getItem('selectedDb1');
    const storedDb2 = localStorage.getItem('selectedDb2');
    if (storedDb1) $('#db1').val(storedDb1);
    if (storedDb2) $('#db2').val(storedDb2);

    function initializeComparison() {
        $('#select-all').change(function() {
            $('.table-select').prop('checked', $(this).prop('checked'));
        });

        $('.compare-with').change(function() {
            const tableEntry = $(this).closest('.table-entry');
            const originalTable = tableEntry.data('table');
            const compareWith = $(this).val();
            
            $.ajax({
                url: 'ajax/compare_single_table.php',
                method: 'POST',
                data: {
                    db1: $('#db1').val(),
                    db2: $('#db2').val(),
                    table1: originalTable,
                    table2: compareWith
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.error) {
                            alert(result.error);
                            return;
                        }
                        if (result.html) {
                            tableEntry.replaceWith(result.html);
                            initializeComparison(); // Reinitialize events
                        }
                    } catch(e) {
                        console.error('Error parsing response:', e);
                        alert('Error updating comparison');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    alert('Error updating comparison');
                }
            });
        });

        $('.compare-details').click(function() {
            const tableEntry = $(this).closest('.table-entry');
            const table = tableEntry.data('table');
            const compareWith = tableEntry.find('.compare-with').val();
            // Table details functionality will be implemented later
            alert('Details for table: ' + table + ' compared with: ' + compareWith);
        });
    }

    $('#db1, #db2').change(function() {
        loadComparison();
    });

    if ($('#db1').val() && $('#db2').val()) {
        loadComparison();
    }
});
