<article class="detail">
  <a class="back-link" href="/referrals">← Back to referral links</a>
  <div class="detail-head">
    <span class="store-badge store-badge-ref"><?= e($referral['program_name']) ?></span>
    <h1><?= e($referral['title']) ?></h1>
    <p class="muted">Shared <?= e(time_ago($referral['created_at'])) ?> by <?= e($referral['submitted_by'] ?? 'team') ?>
       · <?= (int) $referral['clicks'] ?> clicks · <?= e($referral['category']) ?></p>
    <?php if (!empty($referral['reward_text'])): ?>
      <p class="chip chip-reward chip-lg">🎁 <?= e($referral['reward_text']) ?></p>
    <?php endif; ?>
  </div>

  <div class="reveal-box">
    <div class="code-row code-row-lg">
      <code class="deal-code deal-code-wide" id="main-link"><?= e($referral['link']) ?></code>
      <button class="btn btn-copy" type="button" data-copy="<?= e($referral['link']) ?>">📋 Copy Link</button>
    </div>
    <a class="btn btn-primary btn-lg" href="/go/referral/<?= (int) $referral['id'] ?>" target="_blank" rel="noopener nofollow ugc">
      Open Referral Page →
    </a>
  </div>

  <?php if (!empty($referral['description'])): ?>
    <div class="detail-desc"><h2>About this referral</h2><p><?= nl2br(e($referral['description'])) ?></p></div>
  <?php endif; ?>

  <p class="fineprint muted">This is a community-shared referral link. Using it may reward the sharer — that's how we keep the deals coming!</p>
</article>
