<article class="card referral-card">
  <div class="card-top">
    <span class="store-badge store-badge-ref"><?= e($r['program_name']) ?></span>
    <?php if (!empty($r['reward_text'])): ?><span class="chip chip-reward"><?= e($r['reward_text']) ?></span><?php endif; ?>
  </div>
  <h3 class="card-title"><a href="/referral/<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a></h3>
  <p class="card-desc"><?= e(mb_substr((string) $r['description'], 0, 120)) ?><?= mb_strlen((string) $r['description']) > 120 ? '…' : '' ?></p>
  <div class="code-row">
    <code class="deal-code deal-code-link" title="Referral link"><?= e(mb_substr((string) $r['link'], 0, 34)) ?>…</code>
    <button class="btn btn-copy btn-sm" type="button" data-copy="<?= e($r['link']) ?>">Copy Link</button>
  </div>
  <div class="card-foot">
    <a class="btn btn-primary btn-sm" href="/go/referral/<?= (int) $r['id'] ?>" target="_blank" rel="noopener nofollow ugc">Visit →</a>
    <span class="muted"><?= (int) $r['clicks'] ?> clicks</span>
  </div>
</article>
