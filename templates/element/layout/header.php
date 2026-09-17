<header class="site-header">
    <div class="site-header__inner">
        <?= $this->Html->link('Lesson Planner', '/', ['class' => 'site-header__brand']) ?>
        <nav class="site-header__nav">
            <?= $this->Html->link('Home', ['controller' => 'Home', 'action' => 'index']) ?>
            <?php $identity = $this->request->getAttribute('identity'); ?>
            <?php if ($identity): ?>
                <?= $this->Html->link('Profile', ['_name' => 'profile']) ?>
                <?php if ($identity->get('role') === 'admin'): ?>
                    <?= $this->Html->link('Admin panel', ['_name' => 'admin']) ?>
                <?php endif; ?>
                <?= $this->Form->postLink('Log out', ['_name' => 'logout']) ?>
            <?php else: ?>
                <?= $this->Html->link('Log in', ['_name' => 'login']) ?>
                <?= $this->Html->link('Register', ['_name' => 'register']) ?>
            <?php endif; ?>
            <button type="button" id="theme-toggle" class="theme-toggle" aria-pressed="false" aria-label="Switch to dark theme" title="Switch to dark theme">
                <svg class="theme-toggle__icon" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false">
                    <circle cx="10" cy="10" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 2a8 8 0 0 0 0 16z" fill="currentColor"/>
                    <rect x="9" y="1" width="2" height="18" fill="currentColor"/>
                </svg>
            </button>
        </nav>
    </div>
</header>
