<?= $this->extend('layouts/admin') ?>

<?= $this->section('page_title') ?>
DEBUG: Cliente
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3>Debug Cliente Show</h3>
            </div>
            <div class="card-body">
                <h4>Cliente ID: <?= $cliente->id ?></h4>
                <p>Nombre: <?= $cliente->getNombreCompleto() ?></p>
                <p>Email: <?= $cliente->email ?></p>
                <p>User ID: <?= $cliente->user_id ?></p>
                <p>Activo: <?= $cliente->isActivo() ? 'SÍ' : 'NO' ?></p>
                
                <hr>
                
                <a href="<?= site_url('/admin/clientes') ?>" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>