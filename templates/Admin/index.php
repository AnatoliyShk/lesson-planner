<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Admin panel');
?>
<section class="hero">
    <span class="hero__eyebrow">Admin panel</span>
    <h1>Manage</h1>
    <p class="hero__lede">
        Administrative access to lessons, teachers, and user accounts.
    </p>
</section>

<section class="section">
    <div class="section__title">
        <h2>Manage</h2>
    </div>
    <div class="grid">
        <div class="col-4">
            <?= $this->Html->link('Lessons →', ['controller' => 'Lessons', 'action' => 'index'], ['class' => 'button-swiss']) ?>
        </div>
        <div class="col-4">
            <?= $this->Html->link('Teachers →', ['controller' => 'Teachers', 'action' => 'index'], ['class' => 'button-swiss']) ?>
        </div>
        <div class="col-4">
            <?= $this->Html->link('Users →', ['controller' => 'Users', 'action' => 'index'], ['class' => 'button-swiss']) ?>
        </div>
    </div>
</section>
