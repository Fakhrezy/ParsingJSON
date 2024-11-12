<?php
    $koneksi = new mysqli("localhost", "root", "", "iot_db"); 
    if($koneksi -> connect_error){
        echo "Koneksi belum terhubung";
    }
    // echo "Koneksi berhasil";
?>