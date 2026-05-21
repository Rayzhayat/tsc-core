<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (!isset($metadata) || empty($metadata)) return;
?>
<?php foreach ($metadata['features'] as $feat): ?>
    <?php
    $isCat  = in_array($feat, $metadata['categorical']);
    $label  = str_replace('_', ' ', $feat);
    ?>
    <div class="mb-2">
        <label class="form-label small fw-semibold mb-1"><?= htmlspecialchars($label) ?></label>
        <?php if ($isCat): ?>
            <?php $classes = array_filter($metadata['label_encoders'][$feat]['classes'] ?? [], fn($c) => $c !== 'UNKNOWN') ?>
            <select name="<?= htmlspecialchars($feat) ?>" class="form-select form-select-sm">
                <option value="">— Pilih —</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach ?>
            </select>
        <?php else: ?>
            <?php $median = $metadata['num_medians'][$feat] ?? 0 ?>
            <input type="number" name="<?= htmlspecialchars($feat) ?>"
                   class="form-control form-control-sm"
                   placeholder="Default: <?= number_format($median, 0, ',', '.') ?>"
                   step="any">
        <?php endif ?>
    </div>
<?php endforeach ?>