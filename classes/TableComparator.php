<?php
require_once __DIR__ . '/../init.php';

class TableComparator {
    private $db;
    private $db1_name;
    private $db2_name;
    private $tables1;
    private $tables2;
    private $tables1_lookup;
    private $tables2_lookup;
    private $stats;

    public function __construct(Database $db, $db1_name, $db2_name) {
        $this->db = $db;
        $this->db1_name = $db1_name;
        $this->db2_name = $db2_name;
        $this->initialize();
    }

    private function initialize() {
        $this->tables1 = $this->db->getTables($this->db1_name);
        $this->tables2 = $this->db->getTables($this->db2_name);
        $this->tables1_lookup = array_column($this->tables1, null, 'TABLE_NAME');
        $this->tables2_lookup = array_column($this->tables2, null, 'TABLE_NAME');
        $this->calculateStats();
    }

    private function calculateStats() {
        $this->stats = [
            'total1' => count($this->tables1),
            'total2' => count($this->tables2),
            'identical' => 0,
            'missing_db1' => count($this->getMissingInDb1()),
            'missing_db2' => count($this->getMissingInDb2())
        ];

        // Calculate identical tables
        foreach ($this->tables1 as $table) {
            if ($this->tableExistsInDb2($table['TABLE_NAME'])) {
                $comparison = $this->compareTableStructure($table['TABLE_NAME']);
                if ($comparison['identical']) {
                    $this->stats['identical']++;
                }
            }
        }
    }

    public function compareTableStructure($table1, $table2 = null, $force_fresh = false) {
        $compare_with = $table2 ?? $table1;
        
        // Always get fresh data if forced
        $columns1 = $this->db->getTableColumns($this->db1_name, $table1);
        $columns2 = $this->db->getTableColumns($this->db2_name, $compare_with);
        $indexes1 = $this->db->getTableIndexes($this->db1_name, $table1);
        $indexes2 = $this->db->getTableIndexes($this->db2_name, $compare_with);

        $cols1 = array_column($columns1, null, 'COLUMN_NAME');
        $cols2 = array_column($columns2, null, 'COLUMN_NAME');
        $idx1 = array_column($indexes1, null, 'INDEX_NAME');
        $idx2 = array_column($indexes2, null, 'INDEX_NAME');

        $differences = [];
        if ($cols1 != $cols2) $differences[] = 'columns';
        if ($idx1 != $idx2) $differences[] = 'indexes';

        return [
            'identical' => empty($differences),
            'differences' => $differences,
            'columns1' => $cols1,
            'columns2' => $cols2,
            'indexes1' => $idx1,
            'indexes2' => $idx2
        ];
    }

    public function getTableDifferences($table1, $table2 = null, $force_fresh = false) {
        $comparison = $this->compareTableStructure($table1, $table2, $force_fresh);
        $diffs = [
            'columns_missing' => [],
            'type_mismatches' => [],
            'indexes' => []
        ];

        if (isset($comparison['columns1']) && isset($comparison['columns2'])) {
            $missing_in_db2 = array_diff_key($comparison['columns1'], $comparison['columns2']);
            $missing_in_db1 = array_diff_key($comparison['columns2'], $comparison['columns1']);
            
            if (!empty($missing_in_db1)) {
                $diffs['columns_missing'][] = [
                    'label' => T('Missing in DB1'),
                    'class' => 'diff-missing',
                    'content' => implode(', ', array_keys($missing_in_db1))
                ];
            }
            if (!empty($missing_in_db2)) {
                $diffs['columns_missing'][] = [
                    'label' => T('Missing in DB2'),
                    'class' => 'diff-missing',
                    'content' => implode(', ', array_keys($missing_in_db2))
                ];
            }

            // Check type mismatches
            foreach ($comparison['columns1'] as $col => $details) {
                if (isset($comparison['columns2'][$col]) && 
                    $details['COLUMN_TYPE'] !== $comparison['columns2'][$col]['COLUMN_TYPE']) {
                    $diffs['type_mismatches'][] = [
                        'label' => 'Type',
                        'class' => 'diff-type',
                        'content' => sprintf(
                            '%s: %s → %s',
                            $col,
                            $details['COLUMN_TYPE'],
                            $comparison['columns2'][$col]['COLUMN_TYPE']
                        )
                    ];
                }
            }
        }

        // Check indexes
        if (!empty($comparison['indexes1']) || !empty($comparison['indexes2'])) {
            $missing_idx_db2 = array_diff_key($comparison['indexes1'], $comparison['indexes2']);
            $missing_idx_db1 = array_diff_key($comparison['indexes2'], $comparison['indexes1']);

            if (!empty($missing_idx_db1)) {
                $diffs['indexes'][] = [
                    'label' => T('Indexes DB1'),
                    'class' => 'diff-index',
                    'content' => implode(', ', array_keys($missing_idx_db1))
                ];
            }
            if (!empty($missing_idx_db2)) {
                $diffs['indexes'][] = [
                    'label' => T('Indexes DB2'),
                    'class' => 'diff-index',
                    'content' => implode(', ', array_keys($missing_idx_db2))
                ];
            }
        }

        return array_merge(
            $diffs['columns_missing'],
            $diffs['type_mismatches'],
            $diffs['indexes']
        );
    }

    public function getStats() {
        return $this->stats;
    }

    public function getMissingInDb1() {
        return array_keys(array_diff_key($this->tables2_lookup, $this->tables1_lookup));
    }

    public function getMissingInDb2() {
        return array_keys(array_diff_key($this->tables1_lookup, $this->tables2_lookup));
    }

    public function getStructureMismatches() {
        $mismatches = [];
        foreach ($this->tables1 as $table) {
            $table_name = $table['TABLE_NAME'];
            if (isset($this->tables2_lookup[$table_name])) {
                $comparison = $this->compareTableStructure($table_name);
                if (!$comparison['identical']) {
                    $mismatches[] = $table_name . ' (' . implode(', ', $comparison['differences']) . ')';
                }
            }
        }
        return $mismatches;
    }

    public function getTables1() {
        return $this->tables1;
    }

    public function getTables2() {
        return $this->tables2;
    }

    public function tableExistsInDb2($table_name) {
        return isset($this->tables2_lookup[$table_name]);
    }

    public function tableExistsInDb1($table_name) {
        return isset($this->tables1_lookup[$table_name]);
    }

    public function generateTableSelect($selectedTable = null) {
        $select = '<select class="form-select form-select-sm compare-with" style="width: auto; display: inline-block; margin-left: 10px;">';
        foreach ($this->getTables2() as $table) {
            $tableName = $table['TABLE_NAME'];
            $selected = ($tableName === $selectedTable) ? 'selected' : '';
            $select .= sprintf(
                '<option value="%s" %s>%s</option>',
                htmlspecialchars($tableName),
                $selected,
                htmlspecialchars($tableName)
            );
        }
        $select .= '</select>';
        return $select;
    }
}
