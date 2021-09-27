<?php
include('../../koneksi/koneksi.php');

// include all core classes
include("oneTap.php");
include("normalTap.php");

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With,Accept');

$kode = $_GET['kode'];

class Result
{
    function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    function cekkodePeg($kode)
    {
        $sql = "SELECT kode FROM pegawai WHERE kode = '$kode'";
        $ql = $this->koneksi->query($sql);
        $jql = $ql->num_rows;

        if ($jql == 0) {
            return "no";
        } else {
            return "yes";
        }
    }

    function sql($kode)
    {
        date_default_timezone_set('Asia/Jakarta');
        $date = new DateTime('NOW');

        if (!$kode) {
            $statabsen = "Server Error";
        } else {
            if ($this->cekkodePeg($kode) == "no") {
                $statabsen = "Server Error";
            } else {
                if (strpos($kode, 'ACT') !== false | strpos($kode, 'MNG') !== false | strpos($kode, 'DRT') !== false | strpos($kode, 'WDR') !== false | strpos($kode, 'ITD-20210001') !== false | strpos($kode, 'CMS-20210002') !== false | strpos($kode, 'OBN-20210003') !== false) {
                    $result = new oneTap($this->koneksi, $kode);
                    $statabsen = $result->sql($kode);
                } else if (strpos($kode, 'SCT') !== false) {
                    // $security = securityTap($koneksi,$kode);
                    // $statabsen = $security;
                    $statabsen = "security tap";
                } else {
                    
                    $result = new normalTap($this->koneksi, $kode);
                    $statabsen = $result->sql($kode, $date);
                }
            }
        }
        return array(
            "title" => "<h1>" . $statabsen . "</h1>",
            "image" => "<img src='https://kolik.pastigo.co.id/api/karyawan/foto/$kode.png'>"
        );
    }
}

$result = new Result($koneksi, $kode);

header('Content-Type: application/json');
var_dump($result->sql($kode));
$koneksi->close();
?>