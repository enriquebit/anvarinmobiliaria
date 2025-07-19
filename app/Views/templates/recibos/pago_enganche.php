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
    <label>Fecha Pago:</label>
    <span><?= $fecha_formateada ?></span>
</div>
<div class="campo">
    <label>Tipo Ingreso:</label>
    <span><?= ucfirst($tipo_ingreso ?? 'Enganche') ?></span>
</div>
<?php if (!empty($venta)): ?>
<div class="campo">
    <label>Folio Venta:</label>
    <span><?= esc($venta->folio_venta ?? 'N/A') ?></span>
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
    <span><?= ucfirst($ingreso->tipo_ingreso ?? 'enganche') ?></span>
</div>
<?php if (!empty($venta)): ?>
<div class="campo">
    <label>ID Venta:</label>
    <span>#<?= $venta->id ?? 'N/A' ?></span>
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
<p>• Pago de enganche registrado: <?= $fecha_formateada ?></p>
<p>• Tipo de pago: <?= ucfirst($ingreso->tipo_ingreso ?? 'enganche') ?></p>
<?php if (!empty($lote)): ?>
<p>• Lote: <?= esc($lote->clave ?? 'N/A') ?> (<?= esc($lote->area ?? 0) ?>m²)</p>
<?php endif; ?>
<?php if (!empty($venta)): ?>
<p>• Venta asociada: <?= esc($venta->folio_venta ?? 'N/A') ?></p>
<?php endif; ?>
<p>• Método de pago: <?= ucfirst($ingreso->metodo_pago ?? 'efectivo') ?></p>
<p>• Referencia: <?= esc(($ingreso->referencia && $ingreso->referencia !== '0') ? $ingreso->referencia : 'Sin referencia') ?></p>
<?= $this->endSection() ?>