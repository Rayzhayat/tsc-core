<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_prediction
 * Model CI3 untuk prediksi margin menggunakan XGBoost JSON model.
 * Taruh library XGBoostPredictor.php di application/libraries/
 * Taruh file model JSON di assets/models/
 */
class M_prediction extends CI_Model
{
    // Path folder model JSON (relatif dari FCPATH = root public_html)
    private $model_dir;

    // Cache predictor yang sudah di-load (biar gak baca file berulang)
    private $predictors = [];

    // Mapping sheet_type → nama file JSON
    private $sheet_map = [
        'Dailyrent' => 'Dailyrent',
        'FTL_Non_SPX' => 'FTL_Non_SPX',
        'FTL_A1_SPX' => 'FTL_A1_SPX',
        'FTL_Dedicated' => 'FTL_Dedicated',
        'FTL_COC_SPX' => 'FTL_COC_SPX',
        'FTL_Reguler_SPX' => 'FTL_Reguler_SPX',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->model_dir = FCPATH . 'assets/models/';

        // Load library XGBoostPredictor
        require_once(APPPATH . 'libraries/XGBoostPredictor.php');
    }

    // ── Load predictor untuk sheet tertentu ──
    private function _get_predictor($sheet_type)
    {
        if (isset($this->predictors[$sheet_type])) {
            return $this->predictors[$sheet_type];
        }

        $slug = $this->sheet_map[$sheet_type] ?? null;
        if (!$slug) {
            throw new Exception("Sheet type tidak dikenal: $sheet_type");
        }

        $model_path = $this->model_dir . "model_{$slug}.json";
        $metadata_path = $this->model_dir . "metadata_{$slug}.json";

        $predictor = new XGBoostPredictor($model_path, $metadata_path);
        $this->predictors[$sheet_type] = $predictor;
        return $predictor;
    }

    // ── Prediksi margin untuk satu input ──
    public function predict_margin($sheet_type, $input)
    {
        try {
            $predictor = $this->_get_predictor($sheet_type);
            $predicted = $predictor->predict($input);
            $eval = $predictor->get_eval();

            return [
                'success' => true,
                'sheet_type' => $sheet_type,
                'predicted' => $predicted,
                'mae' => $eval['mae'] ?? null,
                'r2' => $eval['r2'] ?? null,
                'mape' => $eval['mape'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // ── Ambil daftar opsi untuk dropdown (dari label encoder) ──
    public function get_options($sheet_type, $feature)
    {
        try {
            $predictor = $this->_get_predictor($sheet_type);
            return $predictor->get_categorical_options($feature);
        } catch (Exception $e) {
            return [];
        }
    }

    // ── Cek model mana saja yang tersedia ──
    public function get_available_models()
    {
        $available = [];
        foreach ($this->sheet_map as $sheet_type => $slug) {
            $model_path = $this->model_dir . "model_{$slug}.json";
            $metadata_path = $this->model_dir . "metadata_{$slug}.json";
            $exists = file_exists($model_path) && file_exists($metadata_path);

            $eval = null;
            if ($exists) {
                try {
                    $predictor = $this->_get_predictor($sheet_type);
                    $eval = $predictor->get_eval();
                } catch (Exception $e) {
                }
            }

            $available[] = [
                'sheet_type' => $sheet_type,
                'slug' => $slug,
                'available' => $exists,
                'eval' => $eval,
            ];
        }
        return $available;
    }

    // ── Ambil metadata lengkap (fitur, kategori, dll) ──
    public function get_metadata($sheet_type)
    {
        try {
            $predictor = $this->_get_predictor($sheet_type);
            return $predictor->get_metadata();
        } catch (Exception $e) {
            return null;
        }
    }
}