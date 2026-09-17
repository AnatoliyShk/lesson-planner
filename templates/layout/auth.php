<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title') ?></title>
    <?= $this->fetch('meta') ?>
    <script>
        (function () {
            var stored = null;
            try { stored = localStorage.getItem('theme'); } catch (e) {}
            document.documentElement.setAttribute('data-theme', stored === 'dark' ? 'dark' : 'light');
        })();
    </script>
    <?= $this->Html->css('swiss') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
    <?php /* Credential pages drop the site nav: nothing but the form to act on. */ ?>
    <header class="site-header">
        <div class="site-header__inner">
            <?= $this->Html->link('Lesson Planner', '/', ['class' => 'site-header__brand']) ?>
            <nav class="site-header__nav">
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
    <?= $this->Flash->render() ?>
    <main>
        <?= $this->fetch('content') ?>
    </main>
    <?= $this->element('layout/footer') ?>
    <?= $this->Html->script('theme') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
