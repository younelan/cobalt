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
        // Remove all existing event handlers and rebind
        $(document).off('change', '#select-all, .compare-with');
        $(document).off('click', '.compare-details');

        // Handle select all
        $(document).on('change', '#select-all', function() {
            $('.table-select').prop('checked', $(this).prop('checked'));
        });

        // Handle table comparison dropdown
        $(document).on('change', '.compare-with', function() {
            const tableRow = $(this).closest('.table-entry');
            const originalTable = tableRow.data('table');  // Get the original table name
            const compareWith = $(this).val();

            if (!compareWith) return;

            $.ajax({
                url: 'ajax/compare_single_table.php',
                method: 'POST',
                data: {
                    db1: $('#db1').val(),
                    db2: $('#db2').val(),
                    table1: originalTable,      // Use the original table name
                    table2: compareWith,        // Use the selected comparison table
                    selected: compareWith       // Keep track of selected value
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.error) {
                            alert(result.error);
                            return;
                        }
                        // Replace the table row with the new comparison
                        const newRow = $(result.html);
                        tableRow.replaceWith(newRow);
                        // Make sure the dropdown keeps the selected value
                        newRow.find('.compare-with').val(compareWith);
                    } catch(e) {
                        console.error('Error parsing response:', e);
                        alert('Error updating comparison');
                    }
                }
            });
        });

        // Handle details button
        $(document).on('click', '.compare-details', function() {
            const tableRow = $(this).closest('.table-entry');
            const table = tableRow.attr('id').replace('table-row-', '');
            const compareWith = tableRow.find('.compare-with').val();
            alert('Details for ' + table + ' compared with ' + compareWith);
        });

        // Handle view switching with improved state management
        $(document).on('click', '.view-summary, .view-details', function(e) {
            e.preventDefault();
            const tableRow = $(this).closest('.table-entry');
            const table1 = tableRow.data('table');
            const table2 = tableRow.find('.compare-with').val();
            const viewType = $(this).hasClass('view-summary') ? 'summary' : 'details';
            
            if (viewType === 'summary') {
                // For summary view, make a fresh comparison to get proper state
                $.ajax({
                    url: 'ajax/compare_single_table.php',
                    method: 'POST',
                    data: {
                        db1: $('#db1').val(),
                        db2: $('#db2').val(),
                        table1: table1,
                        table2: table2,
                        selected: table2,
                        force_fresh: true,
                        view: 'summary'
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.html) {
                                tableRow.replaceWith(result.html);
                            }
                        } catch(e) {
                            console.error('Error:', e);
                        }
                    }
                });
            } else {
                // For detailed view, load the detailed comparison
                $.ajax({
                    url: 'ajax/get_table_details.php',
                    method: 'POST',
                    data: {
                        db1: $('#db1').val(),
                        db2: $('#db2').val(),
                        table1: table1,
                        table2: table2,
                        selected: table2
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.html) {
                                tableRow.replaceWith(result.html);
                            }
                        } catch(e) {
                            console.error('Error:', e);
                        }
                    }
                });
            }

            // Update dropdown active state
            tableRow.find('.dropdown-item').removeClass('active');
            tableRow.find('.view-' + viewType).addClass('active');
        });
    }

    $('#db1, #db2').change(function() {
        loadComparison();
    });

    if ($('#db1').val() && $('#db2').val()) {
        loadComparison();
    }
});
