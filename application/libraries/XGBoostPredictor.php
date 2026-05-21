<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * XGBoostPredictor
 * Membaca model XGBoost format JSON dan menjalankan prediksi di PHP.
 * Tidak butuh Python/Flask — semua pure PHP tree traversal.
 *
 * Usage:
 *   $predictor = new XGBoostPredictor($model_path, $metadata_path);
 *   $result = $predictor->predict($input_array);
 */
class XGBoostPredictor
{
    private $trees = [];
    private $metadata = [];
    private $base_score = 0.5;
    private $objective = 'reg:squarederror';

    public function __construct($model_path, $metadata_path)
    {
        if (!file_exists($model_path)) {
            throw new Exception("Model file tidak ditemukan: $model_path");
        }
        if (!file_exists($metadata_path)) {
            throw new Exception("Metadata file tidak ditemukan: $metadata_path");
        }

        $model_json = json_decode(file_get_contents($model_path), true);
        $this->metadata = json_decode(file_get_contents($metadata_path), true);

        $this->_parse_model($model_json);
    }

    // ── Parse model XGBoost JSON format ──
    private function _parse_model($model_json)
    {
        // XGBoost JSON format: learner > learner_model_param > base_score
        if (isset($model_json['learner']['learner_model_param']['base_score'])) {
            $this->base_score = (float) $model_json['learner']['learner_model_param']['base_score'];
        }
        if (isset($model_json['learner']['objective']['name'])) {
            $this->objective = $model_json['learner']['objective']['name'];
        }

        // Parse semua trees
        $gradient_booter = $model_json['learner']['gradient_booster'] ?? [];
        $model = $gradient_booter['model'] ?? [];
        $trees_raw = $model['trees'] ?? [];

        foreach ($trees_raw as $tree_raw) {
            $this->trees[] = $this->_parse_tree($tree_raw);
        }
    }

    private function _parse_tree($tree_raw)
    {
        return [
            'left_children' => $tree_raw['left_children'] ?? [],
            'right_children' => $tree_raw['right_children'] ?? [],
            'split_indices' => $tree_raw['split_indices'] ?? [],
            'split_conditions' => $tree_raw['split_conditions'] ?? [],
            'default_left' => $tree_raw['default_left'] ?? [],
            'base_weights' => $tree_raw['base_weights'] ?? [],
        ];
    }

    // ── Traverse satu tree ──
    private function _predict_tree($tree, $features)
    {
        $node = 0;
        $left = $tree['left_children'];
        $right = $tree['right_children'];

        while ($left[$node] !== -1) {
            $feat_idx = $tree['split_indices'][$node];
            $thresh = $tree['split_conditions'][$node];
            $val = $features[$feat_idx] ?? 0;

            if (is_nan($val) || $val === null) {
                // Missing value → ikut default direction
                $node = $tree['default_left'][$node] ? $left[$node] : $right[$node];
            } elseif ($val < $thresh) {
                $node = $left[$node];
            } else {
                $node = $right[$node];
            }
        }

        return $tree['base_weights'][$node];
    }

    // ── Encode input menggunakan label encoder dari metadata ──
    private function _encode_input($raw_input)
    {
        $features_order = $this->metadata['features'];
        $cat_features = $this->metadata['categorical'];
        $num_features = $this->metadata['numerical'];
        $le_map = $this->metadata['label_encoders'] ?? [];
        $medians = $this->metadata['num_medians'] ?? [];

        $encoded = [];

        foreach ($features_order as $feat) {
            $val = $raw_input[$feat] ?? null;

            if (in_array($feat, $cat_features)) {
                // Categorical: cari index di classes
                $classes = $le_map[$feat]['classes'] ?? [];
                $val_str = ($val === null || $val === '') ? 'UNKNOWN' : (string) $val;

                $idx = array_search($val_str, $classes);
                if ($idx === false) {
                    // Nilai tidak dikenal → pakai index UNKNOWN atau 0
                    $unknown_idx = array_search('UNKNOWN', $classes);
                    $idx = ($unknown_idx !== false) ? $unknown_idx : 0;
                }
                $encoded[] = (float) $idx;

            } elseif (in_array($feat, $num_features)) {
                // Numerik: kalau kosong pakai median dari training
                if ($val === null || $val === '') {
                    $encoded[] = (float) ($medians[$feat] ?? 0);
                } else {
                    $encoded[] = (float) str_replace([',', ' '], '', $val);
                }
            } else {
                $encoded[] = 0.0;
            }
        }

        return $encoded;
    }

    // ── Main predict ──
    public function predict($raw_input)
    {
        $features = $this->_encode_input($raw_input);

        $score = $this->base_score;
        foreach ($this->trees as $tree) {
            $score += $this->_predict_tree($tree, $features);
        }

        return round($score);
    }

    // ── Getter metadata ──
    public function get_metadata()
    {
        return $this->metadata;
    }

    public function get_sheet()
    {
        return $this->metadata['sheet'] ?? '';
    }

    public function get_eval()
    {
        return $this->metadata['eval'] ?? [];
    }

    public function get_categorical_options($feature)
    {
        $classes = $this->metadata['label_encoders'][$feature]['classes'] ?? [];
        return array_filter($classes, function ($c) {
            return $c !== 'UNKNOWN'; });
    }
}