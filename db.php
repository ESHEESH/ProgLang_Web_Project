<?php
// Shared setup: session + SQLite database (auto-created, no server config needed)
session_start();

$pdo = new PDO('sqlite:' . __DIR__ . '/users.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function page_start($title) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($title) . '</title>
    <style>
      body{font-family:system-ui,sans-serif;background:#f3f4f6;display:flex;justify-content:center;padding-top:80px;margin:0}
      .card{background:#fff;padding:32px;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);width:340px}
      h2{margin-top:0}
      input{width:100%;padding:10px;margin:6px 0 14px;border:1px solid #ccc;border-radius:6px;box-sizing:border-box}
      button{width:100%;padding:11px;background:#2563eb;color:#fff;border:0;border-radius:6px;font-size:15px;cursor:pointer}
      button:hover{background:#1d4ed8}
      .error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:6px;margin-bottom:14px;font-size:14px}
      .success{background:#dcfce7;color:#166534;padding:10px;border-radius:6px;margin-bottom:14px;font-size:14px}
      a{color:#2563eb}
      p.alt{text-align:center;font-size:14px;margin-bottom:0}
    </style></head><body><div class="card">';
}
function page_end() { echo '</div></body></html>'; }
