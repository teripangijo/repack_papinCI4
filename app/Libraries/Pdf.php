<?php 
// Perhatikan bahwa di CodeIgniter 4, custom library biasanya ditempatkan di app/Libraries
// dan menggunakan namespace App\Libraries.

namespace App\Libraries; // Tambahkan namespace

use Dompdf\Dompdf; // Pastikan Dompdf sudah terinstal via Composer
use CodeIgniter\Config\Services; // Untuk mengakses services seperti view dan response

/**
 * CodeIgniter DomPDF Library
 *
 * Menghasilkan PDF dari HTML di CodeIgniter 4
 *
 * @package        CodeIgniter
 * @subpackage        Libraries
 * @category        Libraries
 * @author        Ardianta Pargo
 * @license        MIT License
 * @link        https://github.com/ardianta/codeigniter-dompdf
 */

class Pdf extends Dompdf
{
    /**
     * Nama file PDF
     * @var string
     */
    public $filename;

    public function __construct()
    {
        parent::__construct();
        $this->filename = "laporan.pdf";
    }

    /**
     * Mengubah view CodeIgniter menjadi HTML.
     *
     * @access    protected
     * @param    string    $view Nama view yang akan dimuat
     * @param    array    $data Data yang akan diteruskan ke view
     * @return    string HTML yang dihasilkan dari view
     */
    protected function generateHtmlFromView(string $view, array $data = []): string
    {
        // Di CodeIgniter 4, Anda bisa menggunakan helper view() atau Services::renderer()
        // untuk memuat view dan mengembalikan output-nya.
        $renderer = Services::renderer();
        return $renderer->setData($data)->render($view);
    }

    /**
     * Memuat view CodeIgniter ke domPDF dan mengalirkan PDF.
     *
     * @access    public
     * @param    string    $view Nama view yang akan dimuat
     * @param    array    $data Data view
     * @param    string    $filename Nama file PDF untuk di-stream (opsional)
     * @param    bool    $attachment Apakah akan di-download atau ditampilkan di browser
     * @return    void
     */
    public function load_view(string $view, array $data = [], string $filename = null, bool $attachment = false)
    {
        // Generate HTML dari view menggunakan metode internal baru
        $html = $this->generateHtmlFromView($view, $data);
        
        $this->loadHtml($html); // Gunakan loadHtml() dari Dompdf
        
        // Render PDF
        $this->render();
        
        // Output PDF yang dihasilkan ke Browser atau untuk diunduh
        $outputFilename = $filename ?? $this->filename;
        $this->stream($outputFilename, ["Attachment" => $attachment]);
    }

    /**
     * Metode bantu untuk mengembalikan instance CodeIgniter lama (tidak lagi diperlukan di CI4).
     * Metode ini dihapus karena CI4 tidak memiliki get_instance().
     * Jika Anda memerlukan akses ke service CI4, gunakan Services::serviceName().
     */
    // protected function ci() {} // Dihapus
}
