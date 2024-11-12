<?php
require_once("koneksi.php");
// http://localhost/PrakIOT/uts/uts.php?Show=summary

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");



if (isset($_GET["Show"]) && $_GET["Show"] == "summary") {
    // Query untuk mendapatkan data max suhu, min suhu, dan rata-rata suhu
    $querySummary = "
        SELECT 
            MAX(suhu) AS suhu_max,
            MIN(suhu) AS suhu_min,
            AVG(suhu) AS suhu_rata
        FROM tb_cuaca
    ";
    $resultSummary = $koneksi->query($querySummary);
    if ($resultSummary->num_rows > 0) {
        $summary = $resultSummary->fetch_assoc();
    } else {
        $summary = []; // Penanganan jika tidak ada data
    }

    // Query untuk mendapatkan data suhu maksimum
    $queryMaxSuhu = "
        SELECT 
            id, 
            suhu, 
            humid, 
            lux AS kecerahan, 
            ts AS timestamp 
        FROM tb_cuaca 
        WHERE suhu = (SELECT MAX(suhu) FROM tb_cuaca)
    ";

    // Query untuk mendapatkan data kelembaban maksimum
    $queryMaxHumid = "
        SELECT 
            id, 
            suhu, 
            humid, 
            lux AS kecerahan, 
            ts AS timestamp 
        FROM tb_cuaca 
        WHERE humid = (SELECT MAX(humid) FROM tb_cuaca)
    ";

    // Menjalankan query untuk suhu maksimum
    $resultMaxSuhu = $koneksi->query($queryMaxSuhu);
    $nilaiSuhuMax = [];
    while ($row = $resultMaxSuhu->fetch_assoc()) {
        $nilaiSuhuMax[] = [
            "id" => (int)$row['id'],
            "suhu" => (int)$row['suhu'],
            "humid" => (int)$row['humid'],
            "kecerahan" => (int)$row['kecerahan'],
            "timestamp" => $row['timestamp']
        ];
    }

    // Menjalankan query untuk kelembaban maksimum
    $resultMaxHumid = $koneksi->query($queryMaxHumid);
    $nilaiHumidMax = [];
    while ($row = $resultMaxHumid->fetch_assoc()) {
        $nilaiHumidMax[] = [
            "id" => (int)$row['id'],
            "suhu" => (int)$row['suhu'],
            "humid" => (int)$row['humid'],
            "kecerahan" => (int)$row['kecerahan'],
            "timestamp" => $row['timestamp']
        ];
    }

    // Gabungkan data suhu maksimum dan kelembaban maksimum
    $nilaiSuhuMaxHumidMax = array_merge($nilaiSuhuMax, $nilaiHumidMax);

    // Query untuk mendapatkan data dengan format bulan dan tahun unik
    $queryMonthYear = "
        SELECT DISTINCT DATE_FORMAT(ts, '%c-%Y') AS month_year 
        FROM tb_cuaca
    ";
    $resultMonthYear = $koneksi->query($queryMonthYear);
    $monthYearMax = [];
    while ($row = $resultMonthYear->fetch_assoc()) {
        $monthYearMax[] = [
            "month_year" => $row['month_year']
        ];
    }

    // Format data ke dalam array sesuai JSON yang diinginkan
    $data = [
        "suhumax" => isset($summary['suhu_max']) ? (int)$summary['suhu_max'] : 0,
        "suhumin" => isset($summary['suhu_min']) ? (int)$summary['suhu_min'] : 0,
        "suhurata" => isset($summary['suhu_rata']) ? round((float)$summary['suhu_rata'], 2) : 0.0,
        "nilai_suhu_max_humid_max" => $nilaiSuhuMaxHumidMax, // Data yang ingin Anda tampilkan
        "month_year_max" => $monthYearMax
    ];

    // Mengonversi array ke JSON
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo "Parameter 'Show' tidak valid atau tidak diatur.";
}

$koneksi->close();
?>
