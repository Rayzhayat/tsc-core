<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{

    protected $dompdf;

    public function __construct()
    {
        // Load Dompdf via Composer autoload
        require_once FCPATH . 'vendor/autoload.php';

        // Configure options
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', FCPATH);
        $options->set('enable_php', false);
        $options->set('enable_javascript', false);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Load HTML content
     */
    public function load_html($html)
    {
        $this->dompdf->loadHtml($html);
    }

    /**
     * Set paper size and orientation
     */
    public function set_paper($size = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->setPaper($size, $orientation);
    }

    /**
     * Render PDF
     */
    public function render()
    {
        $this->dompdf->render();
    }

    /**
     * Output PDF to browser (inline or download)
     * 
     * @param string $filename
     * @param array $options ['Attachment' => 0] for inline, 1 for download
     */
    public function stream($filename = 'document.pdf', $options = [])
    {
        $this->dompdf->stream($filename, $options);
    }

    /**
     * Get PDF output as string
     */
    public function output()
    {
        return $this->dompdf->output();
    }

    /**
     * Save PDF to file
     * 
     * @param string $filepath
     */
    public function save($filepath)
    {
        file_put_contents($filepath, $this->output());
    }
}