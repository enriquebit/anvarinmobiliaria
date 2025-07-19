<?= $this->extend('templates/recibos/base_recibo') ?>

<?= $this->section('datos_especificos_cliente') ?>
<div class="campo">
    <label>Concepto:</label>
    <span><?= esc($descripcion_pago) ?></span>
</div>
<div class="campo">
    <label>Lote:</label>
    <span><?= esc($lote->clave ?? 'N/A') ?> - <?= esc($lote->area ?? 0) ?>m²</span>
</div>
<div class="campo">
    <label>Fecha Apartado:</label>
    <span><?= $fecha_formateada ?></span>
</div>
<div class="campo">
    <label>Vigencia:</label>
    <span><?= esc($validez_apartado ?? '90 días') ?></span>
</div>
<?php if (!empty($apartado)): ?>
<div class="campo">
    <label>Folio Apartado:</label>
    <span><?= esc($apartado->folio_apartado ?? 'N/A') ?></span>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('datos_especificos_admin') ?>
<div class="campo">
    <label>ID Ingreso:</label>
    <span>#<?= $ingreso->id ?? 'N/A' ?></span>
</div>
<div class="campo">
    <label>Folio Ingreso:</label>
    <span><?= esc($ingreso->folio ?? 'N/A') ?></span>
</div>
<div class="campo">
    <label>Tipo:</label>
    <span><?= ucfirst($ingreso->tipo_ingreso ?? 'apartado') ?></span>
</div>
<?php if (!empty($apartado)): ?>
<div class="campo">
    <label>ID Apartado:</label>
    <span>#<?= $apartado->id ?? 'N/A' ?></span>
</div>
<?php endif; ?>
<?php if (!empty($lote)): ?>
<div class="campo">
    <label>ID Lote:</label>
    <span>#<?= $lote->id ?? 'N/A' ?></span>
</div>
<?php endif; ?>
<div class="campo">
    <label>Vendedor ID:</label>
    <span>#<?= $vendedor->id ?? 'N/A' ?></span>
</div>
<?= $this->endSection() ?>

<?= $this->section('observaciones_admin') ?>
<p>• Apartado registrado: <?= $fecha_formateada ?></p>
<p>• Vigencia del apartado: <?= esc($validez_apartado ?? '90 días') ?></p>
<?php if (!empty($lote)): ?>
<p>• Lote apartado: <?= esc($lote->clave ?? 'N/A') ?> (<?= esc($lote->area ?? 0) ?>m²)</p>
<?php endif; ?>
<?php if (!empty($apartado)): ?>
<p>• Apartado: <?= esc($apartado->folio_apartado ?? 'N/A') ?></p>
<?php endif; ?>
<p>• Método de pago: <?= ucfirst($ingreso->metodo_pago ?? 'efectivo') ?></p>
<p>• Referencia: <?= esc(($ingreso->referencia && $ingreso->referencia !== '0') ? $ingreso->referencia : 'Sin referencia') ?></p>
<?= $this->endSection() ?>