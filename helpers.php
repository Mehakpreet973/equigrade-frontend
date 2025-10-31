<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function flash($type, $message) {
    if (!isset($_SESSION['flashes'])) {
        $_SESSION['flashes'] = [];
    }
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

function display_flash() {
    if (!empty($_SESSION['flashes'])) {
        foreach ($_SESSION['flashes'] as $flash) {
            $class = $flash['type'] === 'success' ? 'flash-success' : 'flash-error';
            echo "<div class='flash {$class}'>{$flash['message']}</div>";
        }
        unset($_SESSION['flashes']);
    }
}
?>
