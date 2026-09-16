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
    <?= $this->element('layout/header') ?>
    <?= $this->Flash->render() ?>
    <main>
        <?= $this->fetch('content') ?>
    </main>
    <?= $this->element('layout/footer') ?>
    <?= $this->Html->script('theme') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
