<?php
require_once("koneksi.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (isset($_GET["Show"]) && $_GET["Show"] === "summary") {
    // Fungsi untuk menjalankan query dan mengambil data
    function fetchData($koneksi, $query) {
        $result = $koneksi->query($query);
        return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Query untuk summary data suhu
    $querySummary = "SELECT MAX(suhu) AS suhu_max, MIN(suhu) AS suhu_min, AVG(suhu) AS suhu_rata FROM tb_cuaca";
    $summary = fetchData($koneksi, $querySummary);
    $summary = $summary ? $summary[0] : ['suhu_max' => 0, 'suhu_min' => 0, 'suhu_rata' => 0.0];

    // Query data suhu maksimum dan kelembaban maksimum
    $queryMaxSuhu = "SELECT id, suhu, humid, lux AS kecerahan, ts AS timestamp FROM tb_cuaca WHERE suhu = (SELECT MAX(suhu) FROM tb_cuaca)";
    $nilaiSuhuMax = fetchData($koneksi, $queryMaxSuhu);

    $queryMaxHumid = "SELECT id, suhu, humid, lux AS kecerahan, ts AS timestamp FROM tb_cuaca WHERE humid = (SELECT MAX(humid) FROM tb_cuaca)";
    $nilaiHumidMax = fetchData($koneksi, $queryMaxHumid);

    // Menggabungkan data suhu maksimum dan kelembaban maksimum
    $nilaiSuhuMaxHumidMax = array_merge($nilaiSuhuMax, $nilaiHumidMax);

    // Query untuk mendapatkan data dengan format bulan dan tahun unik
    $queryMonthYear = "SELECT DISTINCT DATE_FORMAT(ts, '%c-%Y') AS month_year FROM tb_cuaca";
    $monthYearMax = fetchData($koneksi, $queryMonthYear);

    // Format data sesuai JSON yang diinginkan
    $data = [
        "suhumax" => (int)$summary['suhu_max'],
        "suhumin" => (int)$summary['suhu_min'],
        "suhurata" => round((float)$summary['suhu_rata'], 2),
        "nilai_suhu_max_humid_max" => $nilaiSuhuMaxHumidMax,
        "month_year_max" => array_map(fn($row) => ["month_year" => $row['month_year']], $monthYearMax)
    ];

    // Mengonversi array ke JSON
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);

} else {
    echo "Parameter 'Show' tidak valid atau tidak diatur.";
}

$koneksi->close();
?>
