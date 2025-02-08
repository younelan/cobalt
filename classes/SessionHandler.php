<?php
class SessionHandler {
    public function setCredentials($data) {
        $_SESSION['db_credentials'] = [
            'host' => $data['host'],
            'username' => $data['username'],
            'password' => $data['password']
        ];
    }

    public function getCredentials() {
        return $_SESSION['db_credentials'] ?? null;
    }

    public function setSelectedDatabases($db1, $db2) {
        $_SESSION['selected_databases'] = [
            'db1' => $db1,
            'db2' => $db2
        ];
    }

    public function getSelectedDatabases() {
        return $_SESSION['selected_databases'] ?? null;
    }
}
