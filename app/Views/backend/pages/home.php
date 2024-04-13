<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>
------------  Page content ----------AAAA

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    console.log("Hola mundo")
</script>
<?= $this->endSection() ?>