<?php
include("../../../koneksi/koneksi.php");
require_once('../../../vendor/autoload.php');

session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With,Accept');

// Import library
use Firebase\JWT\JWT;
use Dotenv\Dotenv;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 404 Not Found');
} else {
    if (empty($_POST["username"]) | empty($_POST["username"])) {
        header('HTTP/1.1 404 Not Found');
    } else {
        //using dotenv
        $dotenv = Dotenv::createImmutable("../../../");
        $dotenv->load();

        $username = $_POST['username'];
        $password = $_POST['password'];

        class Result
        {
            function __construct($koneksi)
            {
                $this->koneksi = $koneksi;
            }

            function sql($username, $password)
            {
                $data = $this->koneksi->select("pegawai", ["kode", "data_akun"], [
                    "kode" => $username,
                    "data_akun[~]" => md5($password)
                ]);

                if (count($data) != 0) {
                    $payload = [
                        'userToken' => json_decode($data[0]['data_akun'])->passwordmd5,
                    ];

                    $access_token = JWT::encode($payload, $_ENV['ACCESS_TOKEN_SECRET']);
                    $_SESSION["userToken"] = $access_token;

                    return array(
                        "icon" => "success",
                        "title" => "Berhasil",
                        "text" => "Selamat data kembali..!",
                        "redirect" => "home"
                    );
                } else {
                    return array(
                        "icon" => "error",
                        "title" => "Gagal",
                        "text" => "Username atau Password anda salah..!",
                        "redirect" => "https://localhost/remakeAbsen/frontend"
                    );
                }
            }
        }

        $result = new Result($koneksi, $username, $password);

        header('Content-Type: application/json');
        echo json_encode($result->sql($username, $password));
        $koneksi->pdo = null;
    }
}
