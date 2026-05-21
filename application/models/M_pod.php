<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model: M_pod (Proof of Delivery)
 * Purpose: Handle POD operations and queries
 */
class M_pod extends CI_Model
{
    // ========================================
    // POD SUBMISSION
    // ========================================

    /**
     * Submit POD (Proof of Delivery)
     */
    public function submit_pod($sj_id, $data)
    {
        $this->db->trans_start();

        // Update surat jalan with POD data
        $update_data = [
            'arrival_time' => $data['arrival_time'] ?? null,
            'unloading_start' => $data['unloading_start'] ?? null,
            'unloading_finish' => $data['unloading_finish'] ?? null,
            'qty_delivered' => $data['qty_delivered'] ?? 0,
            'qty_rejected' => $data['qty_rejected'] ?? 0,
            'receiver_name' => $data['receiver_name'] ?? null,
            'receiver_phone' => $data['receiver_phone'] ?? null,
            'receiver_signature' => $data['receiver_signature'] ?? null,
            'photo_proof' => $data['photo_proof'] ?? null,
            'delivery_condition' => $data['delivery_condition'] ?? 'baik',
            'delivery_notes' => $data['delivery_notes'] ?? null,
            'pod_status' => 'completed',
            'pod_submitted_at' => date('Y-m-d H:i:s'),
            'pod_submitted_by' => $data['submitted_by'] ?? 'System',
            'status' => 'delivered'
        ];

        $this->db->where('id', $sj_id);
        $this->db->update('tb_surat_jalan', $update_data);

        // Add event to timeline
        $this->add_trip_event($sj_id, 'pod_submitted', [
            'notes' => 'POD submitted - Receiver: ' . ($data['receiver_name'] ?? 'Unknown'),
            'created_by' => $data['submitted_by'] ?? 'System'
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Update arrival time
     */
    public function mark_arrived($sj_id, $arrival_time = null, $location = null)
    {
        $this->db->trans_start();

        $data = [
            'arrival_time' => $arrival_time ?: date('Y-m-d H:i:s'),
            'status' => 'arrived'
        ];

        $this->db->where('id', $sj_id);
        $this->db->update('tb_surat_jalan', $data);

        // Add event
        $this->add_trip_event($sj_id, 'arrival', [
            'location_name' => $location['name'] ?? null,
            'location_lat' => $location['lat'] ?? null,
            'location_lng' => $location['lng'] ?? null,
            'notes' => 'Arrived at destination'
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Mark as returning
     */
    public function mark_returning($sj_id, $return_time = null)
    {
        $this->db->trans_start();

        $data = [
            'return_time' => $return_time ?: date('Y-m-d H:i:s'),
            'status' => 'returning'
        ];

        $this->db->where('id', $sj_id);
        $this->db->update('tb_surat_jalan', $data);

        // Add event
        $this->add_trip_event($sj_id, 'return_start', [
            'notes' => 'Started return trip to depot'
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Complete trip (arrived back at depot)
     */
    public function complete_trip($sj_id, $data = [])
    {
        $this->db->trans_start();

        $update_data = [
            'return_arrival' => $data['return_arrival'] ?? date('Y-m-d H:i:s'),
            'actual_distance_km' => $data['actual_distance_km'] ?? null,
            'fuel_consumed_liters' => $data['fuel_consumed_liters'] ?? null,
            'status' => 'completed'
        ];

        $this->db->where('id', $sj_id);
        $this->db->update('tb_surat_jalan', $update_data);

        // Add event
        $this->add_trip_event($sj_id, 'completed', [
            'notes' => 'Trip completed - Distance: ' . ($data['actual_distance_km'] ?? 0) . ' km'
        ]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    // ========================================
    // POD PHOTOS
    // ========================================

    /**
     * Add POD photo
     */
    public function add_pod_photo($sj_id, $photo_data)
    {
        $data = [
            'sj_id' => $sj_id,
            'photo_type' => $photo_data['photo_type'] ?? 'barang',
            'photo_path' => $photo_data['photo_path'],
            'description' => $photo_data['description'] ?? null,
            'uploaded_by' => $photo_data['uploaded_by'] ?? 'System'
        ];

        return $this->db->insert('tb_pod_photos', $data);
    }

    /**
     * Get POD photos
     */
    public function get_pod_photos($sj_id)
    {
        return $this->db
            ->where('sj_id', $sj_id)
            ->order_by('id', 'ASC')
            ->get('tb_pod_photos')
            ->result();
    }

    /**
     * Delete POD photo
     */
    public function delete_pod_photo($photo_id)
    {
        // Get photo path first for file deletion
        $photo = $this->db->get_where('tb_pod_photos', ['id' => $photo_id])->row();
        
        if ($photo) {
            $this->db->delete('tb_pod_photos', ['id' => $photo_id]);
            return $photo->photo_path;
        }
        
        return false;
    }

    // ========================================
    // TRIP EVENTS (Timeline)
    // ========================================

    /**
     * Add trip event
     */
    public function add_trip_event($sj_id, $event_type, $event_data = [])
    {
        $data = [
            'sj_id' => $sj_id,
            'event_type' => $event_type,
            'event_time' => $event_data['event_time'] ?? date('Y-m-d H:i:s'),
            'location_name' => $event_data['location_name'] ?? null,
            'location_lat' => $event_data['location_lat'] ?? null,
            'location_lng' => $event_data['location_lng'] ?? null,
            'notes' => $event_data['notes'] ?? null,
            'created_by' => $event_data['created_by'] ?? 'System'
        ];

        return $this->db->insert('tb_trip_events', $data);
    }

    /**
     * Get trip timeline
     */
    public function get_trip_timeline($sj_id)
    {
        return $this->db
            ->where('sj_id', $sj_id)
            ->order_by('event_time', 'ASC')
            ->get('tb_trip_events')
            ->result();
    }

    // ========================================
    // POD QUERIES
    // ========================================

    /**
     * Get POD details
     */
    public function get_pod_details($sj_id)
    {
        $sj = $this->db
            ->select('
                sj.*,
                d.nama_driver,
                d.no_hp as driver_phone,
                u.no_polisi
            ')
            ->from('tb_surat_jalan sj')
            ->join('drivers d', 'd.id = sj.driver_id', 'left')
            ->join('units u', 'u.id = sj.unit_id', 'left')
            ->where('sj.id', $sj_id)
            ->get()
            ->row();

        if (!$sj) {
            return null;
        }

        // Get photos
        $sj->photos = $this->get_pod_photos($sj_id);

        // Get timeline
        $sj->timeline = $this->get_trip_timeline($sj_id);

        return $sj;
    }

    /**
     * Get pending PODs (not yet submitted)
     */
    public function get_pending_pods($driver_id = null)
    {
        $this->db
            ->select('
                sj.*,
                d.nama_driver,
                u.no_polisi
            ')
            ->from('tb_surat_jalan sj')
            ->join('drivers d', 'd.id = sj.driver_id', 'left')
            ->join('units u', 'u.id = sj.unit_id', 'left')
            ->where('sj.pod_status', 'pending')
            ->where_in('sj.status', ['departed', 'in_transit', 'arrived', 'unloading']);

        if ($driver_id) {
            $this->db->where('sj.driver_id', $driver_id);
        }

        return $this->db
            ->order_by('sj.tanggal', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get completed PODs
     */
    public function get_completed_pods($date_from = null, $date_to = null, $driver_id = null)
    {
        $this->db
            ->select('
                sj.*,
                d.nama_driver,
                u.no_polisi
            ')
            ->from('tb_surat_jalan sj')
            ->join('drivers d', 'd.id = sj.driver_id', 'left')
            ->join('units u', 'u.id = sj.unit_id', 'left')
            ->where('sj.pod_status', 'completed');

        if ($date_from) {
            $this->db->where('sj.pod_submitted_at >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('sj.pod_submitted_at <=', $date_to . ' 23:59:59');
        }

        if ($driver_id) {
            $this->db->where('sj.driver_id', $driver_id);
        }

        return $this->db
            ->order_by('sj.pod_submitted_at', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get POD statistics
     */
    public function get_pod_statistics($date_from = null, $date_to = null)
    {
        $this->db->select('
            COUNT(*) as total_trips,
            SUM(CASE WHEN pod_status = "completed" THEN 1 ELSE 0 END) as completed_pods,
            SUM(CASE WHEN pod_status = "pending" THEN 1 ELSE 0 END) as pending_pods,
            SUM(CASE WHEN delivery_condition = "baik" THEN 1 ELSE 0 END) as good_condition,
            SUM(CASE WHEN delivery_condition = "rusak_sebagian" THEN 1 ELSE 0 END) as partial_damage,
            SUM(CASE WHEN delivery_condition = "rusak" THEN 1 ELSE 0 END) as damaged,
            SUM(CASE WHEN delivery_condition = "kurang" THEN 1 ELSE 0 END) as shortage,
            SUM(qty_delivered) as total_delivered,
            SUM(qty_rejected) as total_rejected,
            AVG(actual_distance_km) as avg_distance,
            SUM(fuel_consumed_liters) as total_fuel,
            AVG(
                TIMESTAMPDIFF(MINUTE, arrival_time, unloading_finish)
            ) as avg_unloading_time_minutes
        ');

        if ($date_from) {
            $this->db->where('tanggal >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('tanggal <=', $date_to);
        }

        $this->db->where('status !=', 'cancelled');

        return $this->db->get('tb_surat_jalan')->row();
    }

    /**
     * Get driver performance (POD-based)
     */
    public function get_driver_pod_performance($driver_id, $date_from = null, $date_to = null)
    {
        $this->db->select('
            COUNT(*) as total_deliveries,
            SUM(CASE WHEN pod_status = "completed" THEN 1 ELSE 0 END) as completed_deliveries,
            SUM(CASE WHEN delivery_condition = "baik" THEN 1 ELSE 0 END) as perfect_deliveries,
            SUM(CASE WHEN delivery_condition != "baik" AND delivery_condition IS NOT NULL THEN 1 ELSE 0 END) as problematic_deliveries,
            AVG(
                TIMESTAMPDIFF(MINUTE, tanggal, arrival_time)
            ) as avg_delivery_time_minutes,
            SUM(actual_distance_km) as total_distance,
            SUM(fuel_consumed_liters) as total_fuel,
            AVG(fuel_consumed_liters / NULLIF(actual_distance_km, 0)) as avg_fuel_efficiency
        ')
        ->where('driver_id', $driver_id)
        ->where('status !=', 'cancelled');

        if ($date_from) {
            $this->db->where('tanggal >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('tanggal <=', $date_to);
        }

        return $this->db->get('tb_surat_jalan')->row();
    }

    /**
     * Check if POD can be submitted
     */
    public function can_submit_pod($sj_id)
    {
        $sj = $this->db->get_where('tb_surat_jalan', ['id' => $sj_id])->row();

        if (!$sj) {
            return ['status' => false, 'message' => 'Surat Jalan tidak ditemukan'];
        }

        if ($sj->pod_status == 'completed') {
            return ['status' => false, 'message' => 'POD sudah pernah di-submit'];
        }

        if (!in_array($sj->status, ['departed', 'in_transit', 'arrived', 'unloading'])) {
            return ['status' => false, 'message' => 'Status Surat Jalan tidak valid untuk submit POD'];
        }

        return ['status' => true, 'message' => 'POD dapat di-submit'];
    }

    /**
     * Get on-time delivery rate
     */
    public function get_otd_rate($date_from = null, $date_to = null)
    {
        // On-Time Delivery: arrived before or at expected time
        // Assuming you have expected_arrival field, if not, skip this

        $this->db->select('
            COUNT(*) as total,
            SUM(CASE WHEN arrival_time <= expected_arrival THEN 1 ELSE 0 END) as on_time
        ')
        ->where('pod_status', 'completed')
        ->where('expected_arrival IS NOT NULL', null, false);

        if ($date_from) {
            $this->db->where('tanggal >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('tanggal <=', $date_to);
        }

        $result = $this->db->get('tb_surat_jalan')->row();

        $otd_rate = 0;
        if ($result && $result->total > 0) {
            $otd_rate = ($result->on_time / $result->total) * 100;
        }

        return [
            'total' => $result->total ?? 0,
            'on_time' => $result->on_time ?? 0,
            'late' => ($result->total ?? 0) - ($result->on_time ?? 0),
            'otd_rate' => round($otd_rate, 2)
        ];
    }
}