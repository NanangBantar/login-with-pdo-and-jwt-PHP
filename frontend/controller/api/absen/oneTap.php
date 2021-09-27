<?php
class oneTap
{
    function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    function cekSudahmasuk($kode)
    {
        $sql = "SELECT keterangan FROM absen WHERE kode = '$kode' AND tanggal = CURDATE()";
        $ql = $this->koneksi->query($sql);
        $rql = $ql->fetch_array(MYSQLI_ASSOC);
        $keterangan = json_decode($rql['keterangan'], true);

        if (!is_array($keterangan)) {
            $sql = "SELECT data_absensi FROM pegawai WHERE kode = '$kode'";
            $ql = $this->koneksi->query($sql);
            $rql = $ql->fetch_array(MYSQLI_ASSOC);
            $dataabensi = json_decode($rql['data_absensi']);

            $jamMasuk = explode(":", $dataabensi->{'Jam Masuk'});
            $jamIstirahat = explode(":", $dataabensi->{'Jam Istirahat'});
            $jamPulang = explode(":", $dataabensi->{'Jam Pulang'});

            $masuk = ($jamMasuk[0] - 1) . ":" . rand(50, 59);
            $masuk = strlen($masuk) == 4 ? "0" . $masuk : $masuk;

            $rand = rand(5, 30);
            $rand = strlen($rand) == 1 ? "0" . $rand : $rand;

            $istirahat = ($jamIstirahat[0]) . ":" . $rand;
            $masukistirahat = ($jamIstirahat[0]) . ":" . rand(50, 59);
            $pulang = ($jamPulang[0]) . ":" . rand(10, 25);

            $keterangan = array(
                'A00' => $masuk,
                'A15' => $masuk,
                'A06' => $istirahat,
                'A10' => $masukistirahat,
                'A07' => $pulang,
            );
            $sql = "UPDATE absen SET masuk = '$masuk', istirahat1 = '$istirahat', istirahat2 = '$masukistirahat', pulang = '$pulang', keterangan = '".json_encode($keterangan)."' WHERE tanggal = CURDATE() AND kode = '$kode'";
            $ql = $this->koneksi->query($sql);
        }

        return "Terima kasih telah melakukan absen hari ini";
    }

    function sql($kode)
    {
        return $this->cekSudahmasuk($kode);
    }
}
?>