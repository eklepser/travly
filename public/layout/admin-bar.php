<?php
/**
 * Простой админ-бар, который можно выводить на всех страницах,
 * когда установлен флаг $isAdmin === true.
 */
?>
<div class="admin-control-bar">
  <div class="admin-bar-left">
    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span> — admin</span>
  </div>
  <div class="admin-bar-actions">
    <a class="admin-btn secondary" href="admin.php">Панель админа</a>
    <a class="admin-btn secondary" href="admin.php?action=add-tour">Добавить тур</a>
    <a class="admin-btn secondary" href="admin.php?action=add-hotel">Добавить отель</a>
  </div>
</div>

