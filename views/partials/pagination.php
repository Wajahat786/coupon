<?php $pages = (int) ceil($total / max(1, $perPage)); if ($pages <= 1) return; ?>
<nav class="pagination" aria-label="Pagination">
<?php for ($i = 1; $i <= min($pages, 20); $i++):
    $qs = http_build_query(array_filter(['q' => $search ?? '', 'cat' => $category ?? '', 'sort' => $sort ?? '', 'page' => $i]));
?>
  <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="?<?= e($qs) ?>"><?= $i ?></a>
<?php endfor; ?>
</nav>
