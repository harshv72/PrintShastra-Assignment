<html>
    <body>
    <?php
        require_once('vendor/autoload.php');
        
        use PhpOffice\PhpSpreadsheet\Spreadsheet;
        use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
        use PhpOffice\PhpSpreadsheet\IOFactory;
        $item = $_REQUEST['item'];
        $export_format = $_REQUEST['export_format'];

        // Username is root
        $user = 'root';
        $password = ''; 
        
        // Database name is gfg
        $database = 'smrusqsn_store'; 
        
        // Server is localhost with
        // port number 3308
        $servername='localhost:3306';
        $mysqli = new mysqli($servername, $user, 
                        $password, $database);
        
        // Checking for connections
        if ($mysqli->connect_error) {
            die('Connect Error (' . 
            $mysqli->connect_errno . ') '. 
            $mysqli->connect_error);
        }

        function prepare_pdf($mysqli,$tables){
            require_once('tcpdf_min/tcpdf.php');
            $obj_pdf = new TCPDF('P',PDF_UNIT,PDF_PAGE_FORMAT,true,'UTF-8',false);
            $obj_pdf->SetCreator("PrintShastra");
            $obj_pdf->SetTitle("PrintShastra pdf");
            $obj_pdf->SetHeaderData('','', PDF_HEADER_TITLE, PDF_HEADER_STRING);
            $obj_pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $obj_pdf->setFooterFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            
            $obj_pdf->SetDefaultMonospacedFont('helvetica');
            $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $obj_pdf->SetMargins(PDF_MARGIN_LEFT, '5', PDF_MARGIN_RIGHT);
            $obj_pdf->setPrintHeader(false);
            $obj_pdf->setPrintFooter(false);
            $obj_pdf->SetAutoPageBreak(TRUE,10);
            $obj_pdf->SetFont('helvetica','', 12);
            for($x=0;$x<count($tables);$x++){
                $obj_pdf->AddPage();
                $obj_pdf->setPage($obj_pdf->getPage());
                $content = prepare_table_content($mysqli,$tables[$x]);
                //print($content);
                // $content .= '<table><tr><td>egssfe</td><td>sdfse</td></tr></table>';
                $obj_pdf->writeHTML($content, true, 0, true, 0);
            }
            ob_end_clean();
            $obj_pdf->Output("sample.pdf");
        }

        function prepare_excel($mysqli,$tables){
            
            $styleArray = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => [
                        'argb' => '87CEEB',
                    ]
                ],
            ];

            $styleArray1 = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $spreadsheet = new Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $activeSheet = $spreadsheet->getActiveSheet();
             
            // $activeSheet->setCellValue('A1', 'Product Name');
            // $activeSheet->setCellValue('B1', 'Product SKU');
            // $activeSheet->setCellValue('C1', 'Product Price');
            $spreadsheet->getProperties()
                                ->setCreator("PrintShastra")
                                ->setLastModifiedBy("PrintShastra")
                                ->setTitle("Tables PrintShastra")
                                ->setSubject("Tables")
                                ->setDescription(
                                    "Contents of "
                                )
                                ->setKeywords("office 2007 openxml php")
                                ->setCategory("Test result file");
            for($x=0;$x<count($tables);$x++){
                if($x != 0){
                    $spreadsheet->createSheet();
                    $spreadsheet->setActiveSheetIndex($x);
                }
                $activeSheet = $spreadsheet->getActiveSheet();
                
                $columns = fetch_table_column_names($mysqli,$tables[$x]);
                // print_r($columns);

                $activeSheet->setCellValue('A1', $tables[$x])->getStyle('A1')->applyFromArray($styleArray);
                $range1 = 'A1';
                $letter = 'A';
                $letterAscii = ord($letter);
                $letterAscii = $letterAscii + count($columns) - 1;
                $letter = chr($letterAscii);
                $range2 = $letter . '1';
                // print($range2);
                $activeSheet->mergeCells("$range1:$range2");
                
                $letter = 'A';
                for($y=0;$y<count($columns);$y++){
                    $activeSheet->setCellValue($letter.'2', $columns[$y])->getStyle($letter.'2')->applyFromArray($styleArray1);;
                    $letterAscii = ord($letter);
                    $letterAscii++;
                    $letter = chr($letterAscii);
                }
                
                $result = fetch_table_data($mysqli,$tables[$x]);
                // echo '<br>';
                // print_r($result);
                if($result->num_rows > 0){
                    $i = 3;
                    while($rows=$result->fetch_assoc())
                    {
                        // print_r($rows);
                        $letter='A';
                        for($y=0;$y<count($columns);$y++){
                            $activeSheet->setCellValue($letter.$i , $rows[$columns[$y]]);
                            $letterAscii = ord($letter);
                            $letterAscii++;
                            $letter = chr($letterAscii);
                        }
                        $i++;
                    }
                }
                
            }
            $spreadsheet->setActiveSheetIndex(0);
            ob_end_clean();
            
            $filename = 'PrintShastra.xlsx';
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename='. $filename);
            header('Cache-Control: max-age=0');
            $Excel_writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $Excel_writer->save('php://output');
            die;
        }
        
        function fetch_table_column_names($mysqli,$tableName){
            $columns = array();
            // SQL query to get all table names from database
            $sql_fetch_column_names = "

                SELECT COLUMN_NAME
            
                FROM INFORMATION_SCHEMA.COLUMNS
            
                WHERE TABLE_NAME = '$tableName'
            
                ORDER BY ORDINAL_POSITION
            ";
            //print($sql_fetch_column_names);

            $result = $mysqli->query($sql_fetch_column_names);
            while($rows=$result->fetch_assoc())
            {
                // print($rows["COLUMN_NAME"]);
                array_push($columns,$rows["COLUMN_NAME"]);
            }
            // print_r($columns);
            // print(count($columns));
            return $columns;

        }

        function fetch_table_data($mysqli,$tableName){
            $sql_fetch_data = "

                SELECT * from $tableName

            ";
            // print($sql);

            $result = $mysqli->query($sql_fetch_data);
            return $result;

        }
        function prepare_table_content ($mysqli, $tableName){
            $content = '';
            $columns = fetch_table_column_names($mysqli,$tableName);
            
            $content .= '<br><div style="text-align:center"> Contents of Table: '.$tableName.'</div><br>';
            $content .= '<table>';

            $content .= '<tr>';
            for($x=0;$x<count($columns);$x++){
                $content .= '<th>'. $columns[$x] . '</th>';
            }
            $content .= '</tr>';

            $result = fetch_table_data($mysqli,$tableName);
            while($rows=$result->fetch_assoc())
            {
                $content .= '<tr>';
                for($x=0;$x<count($columns);$x++){
                    $content .= '<td>'. $rows[$columns[$x]] . '</td>';
                }
                $content .= '</tr>';
            }
            $content .= '</table>';
            $content .= '<hr style="background:red;height:10px"></hr>';
            return $content;
        }

        $tables = array();
        if(strcmp($item,'smrusqsn_store')){
            //print($item);
            array_push($tables,$item);
            //print_r($tables);
        }
        else{
            // SQL query to get all table names from database
            $sql_fetch_table_names = "
                SELECT TABLE_NAME 
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='$database' 
            ";
            //print($sql);

            $result = $mysqli->query($sql_fetch_table_names);
            while($rows=$result->fetch_assoc())
            {
                array_push($tables, $rows['TABLE_NAME']);
            }
        }
        //print_r($tables);

        if(!strcmp($export_format,'pdf')){
            // echo ' in pdf <br>';
            prepare_pdf($mysqli,$tables);
        }
        elseif(!strcmp($export_format,'excel')){
            // echo ' in excel <br>';
            prepare_excel($mysqli,$tables);
        }
        
        
        
        $mysqli->close();

    ?>
    </body>
</html>
