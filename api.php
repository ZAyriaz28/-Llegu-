// Ejemplo básico con PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Aquí es donde conectas con tu BD
$query = "SELECT nombre FROM estudiantes WHERE asistio = 1";
$resultado = mysqli_query($conexion, $query);

$fila_excel = 1;
while($dato = mysqli_fetch_assoc($resultado)) {
    $sheet->setCellValue('A' . $fila_excel, $dato['nombre']);
    $fila_excel++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('Asistencia_Estudiantes.xlsx');
