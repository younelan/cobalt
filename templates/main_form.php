<div class="container-fluid mt-4">
    <form id="dbCompareForm" method="post">
        <div class="row mb-4">
            <div class="col-12">
                <div class="row g-3">
                    <?php require_once 'database_selects.php'; ?>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Compare Selected Tables</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php require_once 'table_lists.php'; ?>
        </div>
    </form>
    
    <div id="comparison-results" class="mt-4"></div>
</div>
