<?= $this->extend('templates/recibos/base_recibo') ?>

<?= $this->section('datos_especificos_cliente') ?>
<div class="campo">
    <label>Concepto:</label>
    <span><?= esc($descripcion_pago) ?></span>
</div>
<div class="campo">
    <label>Tipo Pago:</label>
    <span><?= ucfirst($tipo_ingreso ?? 'General') ?></span>
</div>
<div class="campo">
    <label>Fecha Pago:</label>
    <span><?= $fecha_formateada ?></span>
</div>
<?php if (!empty($lote) && !empty($lote->clave)): ?>
<div class="campo">
    <label>Lote:</label>
    <span><?= esc($lote->clave ?? 'N/A') ?> - <?= esc($lote->area ?? 0) ?>m²</span>
</div>
<?php endif; ?>
<?php if (!empty($periodo_pago)): ?>
<div class="campo">
    <label>Periodo:</label>
    <span><?= esc($periodo_pago) ?></span>
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
    <span><?= ucfirst($ingreso->tipo_ingreso ?? 'general') ?></span>
</div>
<div class="campo">
    <label>Cliente ID:</label>
    <span>#<?= $cliente->id ?? 'N/A' ?></span>
</div>
<?php if (!empty($lote) && !empty($lote->id)): ?>
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
<p>• Pago registrado: <?= $fecha_formateada ?></p>
<p>• Tipo de pago: <?= ucfirst($ingreso->tipo_ingreso ?? 'general') ?></p>
<p>• Método de pago: <?= ucfirst($ingreso->metodo_pago ?? 'efectivo') ?></p>
<p>• Referencia: <?= esc(($ingreso->referencia && $ingreso->referencia !== '0') ? $ingreso->referencia : 'Sin referencia') ?></p>
<?php if (!empty($lote) && !empty($lote->clave)): ?>
<p>• Lote relacionado: <?= esc($lote->clave ?? 'N/A') ?></p>
<?php endif; ?>
<p>• Cliente: <?= esc($cliente->nombres . ' ' . $cliente->apellido_paterno) ?></p>
<?= $this->endSection() ?>