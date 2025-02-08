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
                    $('#comparison-results').html(response);
                    initializeComparison();
                },
                error: function() {
                    $('#comparison-results').html('<div class="alert alert-danger">Error loading comparison. Please try again.</div>');
                }
            });
        } else {
            $('#comparison-results').html('<div class="alert alert-info">Please select two databases to compare.</div>');
        }
    }

    function initializeComparison() {
        $('#select-all').change(function() {
            $('.table-select').prop('checked', $(this).prop('checked'));
        });

        $('.compare-details').click(function() {
            const table = $(this).data('table');
            // Table details functionality will be implemented later
            alert('Details for table: ' + table);
        });
    }

    $('#db1, #db2').change(function() {
        loadComparison();
    });

    if ($('#db1').val() && $('#db2').val()) {
        loadComparison();
    }
});
