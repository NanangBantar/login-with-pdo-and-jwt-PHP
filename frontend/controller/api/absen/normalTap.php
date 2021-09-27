<?php

class normalTap
{
    function __construct($koneksi)
    {
        $this->koneksi = $koneksi;
    }

    function dataAbsen($kode)
    {
        $sql = "SELECT status FROM team WHERE anggotaTeam LIKE '%$kode%'";
        $ql = $this->koneksi->query($sql);
        $jql = $ql->num_rows;

        if ($jql != "0") {
            $rql = $ql->fetch_array(MYSQLI_ASSOC);
            $status = $rql['status'];
            $sql = "SELECT potongan FROM kode_izin WHERE kode = '$status'";
            $ql = $this->koneksi->query($sql);
            $rql = $ql->fetch_array(MYSQLI_ASSOC);

            //set shift worker
            $dataabsensi = json_decode($rql['potongan']);
            $dataabsensi->$status = "Shift Worker";

            return $dataabsensi;
        } else {
            $sql = "SELECT data_absensi FROM pegawai WHERE kode = '$kode'";
            $ql = $this->koneksi->query($sql);
            $rql = $ql->fetch_array(MYSQLI_ASSOC);

            return json_decode($rql['data_absensi']);
        }
    }

    function ketThisDay($kode)
    {
        $param = '{"A28":"Shift Worker"}';
        $sql = "SELECT keterangan FROM absen WHERE kode = '$kode' AND keterangan = '$param' AND tanggal = CURDATE()";

        $ql = $this->koneksi->query($sql);
        $jql = $ql->num_rows;

        if($jql != "0"){
            $sql = "UPDATE absen SET keterangan = '' WHERE kode = '$kode' AND tanggal = CURDATE()";
            $ql = $this->koneksi->query($sql);
            $ql;
        }
    }


    function cekSudahmasuk($kode, $date)
    {
        $sql = "SELECT keterangan FROM absen WHERE kode = '$kode' AND tanggal = CURDATE()";
        $ql = $this->koneksi->query($sql);
        $rql = $ql->fetch_array(MYSQLI_ASSOC);
        $keterangan = json_decode($rql['keterangan'], true);

        //hour sekarang
        // $hourSekarang = (abs($date->format("H")) * 3600) + (abs($date->format("i")) * 60);
        $hourSekarang = 10800;
        $dataabsensi = $this->dataAbsen($kode);

        if (!is_array($keterangan)) {
            $jamMasuk = explode(":", $dataabsensi->{'Jam Masuk'});

            //hour masuk
            $hourMasuk = ($jamMasuk[0] * 3600) + ($jamMasuk[1] * 60);

            if ($hourSekarang > ($hourMasuk + 600)) {
                $telat = "A05";
            } else {
                $telat = "A15";
            }

            $a = "";
            $keterangan = array(
                'A00' => $date->format("H:i"),
                $telat => $date->format("H:i")
            );

            if (strpos($kode, 'DRV') !== false) {
                $jamIstirahat = $dataabsensi->{'Jam Istirahat'};
                $jamselIstirahat = $dataabsensi->{'Jam Selesai Istirahat'};
                $a = "istirahat1 = '$jamIstirahat', istirahat2 = '$jamselIstirahat',";
                $keterangan['A06'] = $jamIstirahat;
                $keterangan['A10'] = $jamselIstirahat;
            }

            if (array_key_exists("A28", $dataabsensi)) {
                end($dataabsensi);
                $keterangan[key($dataabsensi)] = end($dataabsensi);

                // add now
                $sql = "UPDATE absen SET masuk = '" . $keterangan['A00'] . "', $a keterangan = '" . json_encode($keterangan) . "' WHERE kode = '$kode' AND tanggal = CURDATE()";
                $ql = $this->koneksi->query($sql);

                // add tomorow
                $keterangan = array(
                    key($dataabsensi) => end($dataabsensi)
                );
                $sql = "UPDATE absen SET $a keterangan = '" . json_encode($keterangan) . "' WHERE kode = '$kode' AND tanggal = CURDATE() + INTERVAL 1 DAY";
                $ql = $this->koneksi->query($sql);
            } else {
                $sql = "UPDATE absen SET masuk = '" . $keterangan['A00'] . "', $a keterangan = '" . json_encode($keterangan) . "' WHERE kode = '$kode' AND tanggal = CURDATE()";
                $ql = $this->koneksi->query($sql);
            }

            return "Terima kasih telah melakukan absen hari ini";
        } else {
           
            if (in_array("Shift Worker", $keterangan)) {
                $sql = "SELECT keterangan FROM absen WHERE kode = '$kode' AND tanggal = CURDATE() - INTERVAL 1 DAY";
                $ql = $this->koneksi->query($sql);
                $rql = $ql->fetch_array(MYSQLI_ASSOC);
                $keterangan = json_decode($rql['keterangan'], true);
                $u = "CURDATE() - INTERVAL 1 DAY";
            } else {
                $keterangan = $keterangan;
                $u = "CURDATE()";
            }

            if (array_key_exists("A00", $keterangan) | array_key_exists("A05", $keterangan) | array_key_exists("A15", $keterangan)) {
                $jamIstirahat = explode(":", $dataabsensi->{'Jam Istirahat'});
                $jamPulang = explode(":", $dataabsensi->{'Jam Pulang'});
                $istirahat1 = ($jamIstirahat[0] * 3600) + ($jamIstirahat[1] * 60);
                $hourPulang = ($jamPulang[0] * 3600) + ($jamPulang[1] * 60);
                if (!array_key_exists("A06", $keterangan) && $hourSekarang >= $istirahat1 && $hourSekarang <= ($istirahat1 + 1800)) {
                    $keterangan['A06'] = $date->format("H:i");
                    $sql = "UPDATE absen SET istirahat1 = '" . $keterangan['A06'] . "', keterangan = '" . json_encode($keterangan) . "' WHERE kode = '$kode' AND tanggal = $u";
                } else if (!array_key_exists("A10", $keterangan) && $hourSekarang >= ($istirahat1 + 1800) && $hourSekarang <= ($istirahat1 + 4000)) {
                    $keterangan['A10'] = $date->format("H:i");
                    $sql = "UPDATE absen SET istirahat2 = '" . $keterangan['A10'] . "', keterangan = '" . json_encode($keterangan) . "' WHERE kode = '$kode' AND tanggal = $u";
                } else if (!array_key_exists("A07", $keterangan) && $hourSekarang > ($hourPulang - 7200)) {
                    $keterangan['A07'] = $date->format("H:i");
                    $sql = "UPDATE absen SET pulang = '" . $keterangan['A07'] . "', keterangan = '" . json_encode($keterangan) . "' WHERE kode = '$kode' AND tanggal = $u";
                    $this->ketThisDay($kode);
                } else {
                    return "Terima kasih telah melakukan absen hari ini";
                }
                $ql = $this->koneksi->query($sql);
                return "Terima kasih telah melakukan absen hari ini";
            } else {
                return "Terjadi kesalahan pada pendataan absensi anda, tanyakan pada IT Abdul Muis Sumardi";
            }
        }
    }

    function sql($kode, $date)
    {
        return $this->cekSudahmasuk($kode, $date);
    }
}
?>