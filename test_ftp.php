<?php
$ip   = '192.168.0.202';
$port = 21;

echo "Conectando a $ip:$port...\n";
$conn = ftp_connect($ip, $port, 15);
if (!$conn) { echo "FALLO TCP\n"; exit(1); }
echo "Conexion TCP OK!\n";

$login = @ftp_login($conn, 'anonymous', '');
echo $login ? "Login OK!\n" : "Login FALLO\n";

// Modo ACTIVO (sin pasivo)
@ftp_pasv($conn, false);
echo "Modo ACTIVO activado.\n";

$lista = @ftp_nlist($conn, '.');
if ($lista === false) {
    echo "NLIST fallo en modo ACTIVO\n";
} else {
    echo "NLIST OK! Items: " . count($lista) . "\n";
    print_r(array_slice($lista, 0, 10));
}

@ftp_close($conn);
