<?php
/* Call this file 'hello-world.php' */
require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
// $connector = new FilePrintConnector("php://stdout");
$connector = new NetworkPrintConnector("192.168.85.1", 8899);
// $connector = new NetworkPrintConnector("192.168.0.162", 8899);
$printer = new Printer($connector);
try {
    // ... Print stuff
    $printer->text("Assalam o alaikum!\n");
    $printer->cut();
} finally {
    $printer->close();
}
// $printer -> close();




// use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
// use Mike42\Escpos\Printer;
// $printer = new Printer($connector);