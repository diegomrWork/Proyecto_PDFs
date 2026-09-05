<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cartas Co-Propiedad</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; line-height: 1.5; margin: 30px 40px; }
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 15px; }
        .uppercase { text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        
        /* Estilos para firmas en cuadrícula */
        .tabla-firmas { width: 100%; border: none; margin-top: 60px; }
        .tabla-firmas td { border: none; text-align: center; padding-bottom: 40px; }
        .caja-firma-centro {
            width: 220px; 
            margin: 0 auto; 
            border-top: 1px solid #000; 
            padding-top: 5px;
        }
    </style>
</head>
<body>

    @php
        // ==========================================
        // 1. FECHA EN ESPAÑOL
        // ==========================================
        $fecha = \Carbon\Carbon::parse($datos['fecha'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $fecha = ucwords($fecha);
        
        // ==========================================
        // 2. DATOS DE LA EMPRESA -> DEPENDEN DEL STOCK
        // ==========================================
        if (isset($datos['stock']) && $datos['stock'] === 'TRUJILLO') {
            $empresaNombre = "AUTONORT TRUJILLO S.A.C.";
            $empresaRuc = "20396419093";
            $empresaDomicilio = "Av. Nicolás de Piérola N° 684, Urb. Primavera";
            $empresaPartida = "11008115 del Registro Mercantil de Trujillo";
        } else {
            $empresaNombre = "AUTONORT CAJAMARCA S.A.C.";
            $empresaRuc = "20495635822";
            $empresaDomicilio = "Av. Hoyos Rubio Nro. 1272 Campo Real (Carretera Al Aeropuerto) Cajamarca";
            $empresaPartida = "11019851 del Registro Mercantil de Cajamarca";
        }

        // ==========================================
        // 3. APODERADOS Y ZONAS -> DEPENDEN DE LA SUCURSAL (CIUDAD)
        // ==========================================
        $ciudadEvaluada = mb_strtoupper($datos['ciudad']);

        if ($ciudadEvaluada === 'TRUJILLO') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510 y/o <span class='bold'>LISBETH DURAND VARGAS</span>, con DNI N° 40058255";
            $oficinaRegistral = "TRUJILLO";
            $zonaRegistral = "ZONA REGISTRAL V SEDE TRUJILLO";
            $apoderadosRegistral = "la Sra. <span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, identificada con DNI 44550672 y/o a <span class='bold'>ANTHONY GABRIEL QUISPE CHAVEZ</span> identificado con DNI N° 70468745, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "la Sra. <span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, identificada con DNI N° 44550672, y/o <span class='bold'>ANTHONY GABRIEL QUISPE CHAVEZ</span> identificado con DNI N° 70468745, y/o a __________________________________________ con DNI N° __________________________________________";
        
        } elseif ($ciudadEvaluada === 'HUAMACHUCO') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510 y/o <span class='bold'>LISBETH DURAND VARGAS</span>, con DNI N° 40058255";
            $oficinaRegistral = "TRUJILLO";
            $zonaRegistral = "ZONA REGISTRAL V SEDE TRUJILLO";
            $apoderadosRegistral = "la Sra. <span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, identificada con DNI 44550672 y/o a <span class='bold'>ANTHONY GABRIEL QUISPE CHAVEZ</span> identificado con DNI N° 70468745";
            $apoderadosAAP = "la Sra. <span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, identificada con DNI N° 44550672, y/o <span class='bold'>ANTHONY GABRIEL QUISPE CHAVEZ</span> identificado con DNI N° 70468745";
        
        } elseif ($ciudadEvaluada === 'CHIMBOTE') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>JOSE MARTIN CHUMAN CASTILLO</span>, con DNI N° 18071209";
            $oficinaRegistral = "CHIMBOTE";
            $zonaRegistral = "ZONA REGISTRAL VII SEDE CHIMBOTE";
            $apoderadosRegistral = "la Srta. <span class='bold'>PAOLA KATHERINE MORALES GUTIERREZ</span> identificada con DNI N° 71067817, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "la Srta. <span class='bold'>PAOLA KATHERINE MORALES GUTIERREZ</span> identificada con DNI N° 71067817, y/o a __________________________________________ con DNI N° __________________________________________";

        } elseif ($ciudadEvaluada === 'HUARAZ') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>ESTELA ALVARADO QUICAÑO</span>, con DNI N° 29351939, y/o <span class='bold'>JOSE MARTIN CHUMAN CASTILLO</span>, con DNI N° 18071209";
            $oficinaRegistral = "HUARAZ";
            $zonaRegistral = "ZONA REGISTRAL VII SEDE HUARAZ";
            $apoderadosRegistral = "la Sr(a). <span class='bold'>ESTELA ALVARADO QUICAÑO</span>, identificada con DNI N° 29351939, <span class='bold'>YOEL RENZO RIVERA AGUEDO</span>, identificado con DNI N° 76009352, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "la Sr(a). <span class='bold'>ESTELA ALVARADO QUICAÑO</span>, identificada con DNI N° 29351939, <span class='bold'>YOEL RENZO RIVERA AGUEDO</span>, identificado con DNI N° 76009352, y/o a __________________________________________ con DNI N° __________________________________________";

        } elseif ($ciudadEvaluada === 'BARRANCA') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>CECILIA ELIZABETH RODRIGUEZ ARCE</span>, con DNI N° 45953490, y/o <span class='bold'>JOSE MARTIN CHUMAN CASTILLO</span>, con DNI N° 18071209";
            $oficinaRegistral = "CHIMBOTE";
            $zonaRegistral = "ZONA REGISTRAL VII SEDE CHIMBOTE";
            $apoderadosRegistral = "la Srta. <span class='bold'>PAOLA KATHERINE MORALES GUTIERREZ</span> identificada con DNI N° 71067817, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "la Srta. <span class='bold'>PAOLA KATHERINE MORALES GUTIERREZ</span> identificada con DNI N° 71067817, y/o a __________________________________________ con DNI N° __________________________________________";

        } elseif ($ciudadEvaluada === 'CAJAMARCA') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>IVAN ENRIQUE AMAYA HERNANDEZ</span>, con DNI N° 40311575";
            $oficinaRegistral = "CAJAMARCA";
            $zonaRegistral = "ZONA REGISTRAL II SEDE CHICLAYO";
            $apoderadosRegistral = "<span class='bold'>SUSANA PATRICIA ALCANTARA AMBROSIO</span>, identificada con DNI 41317425, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "<span class='bold'>SUSANA PATRICIA ALCANTARA AMBROSIO</span>, identificada con DNI 41317425, y/o a __________________________________________ con DNI N° __________________________________________";
            
        } elseif ($ciudadEvaluada === 'JAÉN') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>WILLER WILLIAM MEGO MONTES</span>, con DNI N° 43809537, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510";
            $oficinaRegistral = "CAJAMARCA";
            $zonaRegistral = "ZONA REGISTRAL II SEDE CHICLAYO";
            $apoderadosRegistral = "la Srta. <span class='bold'>SANDRA HITAMAR GARCIA NEIRA</span>, identificada con DNI 71218453, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "la Srta. <span class='bold'>SANDRA HITAMAR GARCIA NEIRA</span>, identificada con DNI 71218453, y/o a __________________________________________ con DNI N° __________________________________________";
            
        } elseif ($ciudadEvaluada === 'TALARA') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>MARIO ALBERTO OLORTIGA ESQUIVEL</span>, con DNI N° 46907962, y/o <span class='bold'>CESAR EDUARDO CUBAS MONTENEGRO</span>, con DNI N° 40640963, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510";
            $oficinaRegistral = "TRUJILLO";
            $zonaRegistral = "ZONA REGISTRAL V SEDE TRUJILLO";
            $apoderadosRegistral = "el Sr. <span class='bold'>MANUEL JESUS BARTUREN FONSECA</span>, identificada con DNI 40227317, y/o al Sr. <span class='bold'>KEVIN DAVID PAJUELO RIOS</span>, identificada con DNI 75056101, y/o al Sr. <span class='bold'>WILFREDO PICHEN PRETEL</span>, identificada con DNI 46232456, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "el Sr. <span class='bold'>MANUEL JESUS BARTUREN FONSECA</span>, identificada con DNI 40227317, y/o al Sr. <span class='bold'>KEVIN DAVID PAJUELO RIOS</span>, identificada con DNI 75056101, y/o al Sr. <span class='bold'>WILFREDO PICHEN PRETEL</span>, identificada con DNI 46232456, y/o a __________________________________________ con DNI N° __________________________________________";

        } elseif ($ciudadEvaluada === 'TUMBES') {
            $itfNormalCiudad = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>MARIO ALBERTO OLORTIGA ESQUIVEL</span>, con DNI N° 46907962, y/o <span class='bold'>CESAR EDUARDO CUBAS MONTENEGRO</span>, con DNI N° 40640963, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510";
            $oficinaRegistral = "TRUJILLO";
            $zonaRegistral = "ZONA REGISTRAL V SEDE TRUJILLO";
            $apoderadosRegistral = "el Sr. <span class='bold'>MANUEL JESUS BARTUREN FONSECA</span>, identificada con DNI 40227317, y/o al Sr. <span class='bold'>KEVIN DAVID PAJUELO RIOS</span>, identificada con DNI 75056101, y/o al Sr. <span class='bold'>WILFREDO PICHEN PRETEL</span>, identificada con DNI 46232456, y/o a __________________________________________ con DNI N° __________________________________________";
            $apoderadosAAP = "el Sr. <span class='bold'>MANUEL JESUS BARTUREN FONSECA</span>, identificada con DNI 40227317, y/o al Sr. <span class='bold'>KEVIN DAVID PAJUELO RIOS</span>, identificada con DNI 75056101, y/o al Sr. <span class='bold'>WILFREDO PICHEN PRETEL</span>, identificada con DNI 46232456, y/o a __________________________________________ con DNI N° __________________________________________";
        } else {
            $itfNormalCiudad = "<span class='bold'>[FALTA CONFIGURAR]</span>";
            $oficinaRegistral = strtoupper($datos['ciudad']);
            $zonaRegistral = "[FALTA ZONA REGISTRAL]";
            $apoderadosRegistral = "<span class='bold'>[FALTA CONFIGURAR]</span>";
            $apoderadosAAP = "<span class='bold'>[FALTA CONFIGURAR]</span>";
        }

        // ==========================================
        // 4. LÓGICA DE VENTA CRUZADA (ITF)
        // ==========================================
        $redTrujillo = ['TRUJILLO', 'HUAMACHUCO', 'CHIMBOTE', 'HUARAZ', 'BARRANCA'];
        $redCajamarca = ['CAJAMARCA', 'JAÉN', 'TALARA', 'TUMBES'];

        // Si es Stock Trujillo
        if (isset($datos['stock']) && $datos['stock'] === 'TRUJILLO') {
            if (in_array($ciudadEvaluada, $redCajamarca)) {
                // RED CAJAMARCA vende STOCK TRUJILLO (Firman los apoderados principales de Trujillo)
                $apoderadosITF = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510";
            } else {
                // RED TRUJILLO vende STOCK TRUJILLO (Firman los normales de su ciudad)
                $apoderadosITF = $itfNormalCiudad;
            }
        } 
        // Si es Stock Cajamarca
        else {
            if (in_array($ciudadEvaluada, $redTrujillo)) {
                // RED TRUJILLO vende STOCK CAJAMARCA (Firman los apoderados principales de Cajamarca)
                $apoderadosITF = "<span class='bold'>MILAGROS YESENIA MIRANDA ZAVALA</span>, con DNI N° 44550672, y/o <span class='bold'>MARGARITA RAFAELA NECIOSUP ALVAREZ</span>, con DNI N° 17911510";
            } else {
                // RED CAJAMARCA vende STOCK CAJAMARCA (Firman los normales de su ciudad)
                $apoderadosITF = $itfNormalCiudad;
            }
        }

        // ==========================================
        // 5. UNIÓN MÁGICA PARA LA HOJA 1 (ITF)
        // ==========================================
        $textoVendedorITF = "El Vendedor, {$empresaNombre}, con RUC N° {$empresaRuc} y con domicilio legal en {$empresaDomicilio}. Debidamente representada por su Apoderado, {$apoderadosITF}, según facultades inscritas en la Partida Electrónica {$empresaPartida}.";

        // ==========================================
        // 6. LÓGICA DINÁMICA DE COPROPIETARIOS
        // ==========================================
        $compradores = [];
        $nombresCertifico = [];
        $estadosCiviles = [];
        
        foreach($datos['copropiedad']['lista'] as $prop) {
            $compradores[] = "<span class='uppercase bold'>" . $prop['nombre'] . "</span> identificado con " . $prop['tipo_doc'] . " N° " . $prop['dni'];
            $nombresCertifico[] = strtoupper($prop['nombre']) . " IDENTIFICADO CON " . $prop['tipo_doc'] . " " . $prop['dni'];
            
            $txtEstado = "el estado civil de " . strtoupper($prop['nombre']) . " es " . strtoupper($prop['estado_civil']);
            if($prop['estado_civil'] === 'CASADO') {
                $txtEstado .= ", con " . strtoupper($prop['nombre_conyuge']) . ", identificado con " . $prop['tipo_doc_conyuge'] . " N° " . $prop['dni_conyuge'];
            }
            $estadosCiviles[] = $txtEstado;
        }
        
        $cadenaCompradores = implode(' y ', $compradores);
        $cadenaNombresCertifico = implode(' Y A ', $nombresCertifico); 
        $cadenaEstadosCiviles = implode(' y de ', $estadosCiviles);
    @endphp

    <!-- ======================================================= -->
    <!-- HOJA 1: IMPUESTO A LAS TRANSACCIONES FINANCIERAS        -->
    <!-- ======================================================= -->
    <div class="text-right">
        <p>{{ $datos['ciudad'] }}, {{ $fecha }}.</p>
    </div>

    <p>Señores:<br>
    <span class="bold">OFICINA REGISTRAL DE {{ $oficinaRegistral }} ({{ $zonaRegistral }})</span><br>
    Presente.-</p>

    <p><span class="bold">Ref. Dec. Leg. N° 939 (Impuesto a las Transacciones Financieras)</span></p>
    
    <p>De nuestra consideración:</p>

    <div style="text-align: justify">
        <p>{!! $textoVendedorITF !!}</p>
    </div>

    <p>El Comprador, {!! $cadenaCompradores !!}.</p>

    <p>Declaran que la venta de una Vehículo; marca <span class="uppercase">{{ $datos['vehiculo']['marca'] }}</span>, Modelo <span class="uppercase">{{ $datos['vehiculo']['modelo'] }}</span>; se realizó de acuerdo como se detalla a continuación:</p>

    <table>
        <tr>
            <th>Pago N°</th>
            <th>Monto Pagado</th>
            <th>Moneda</th>
            <th>Tipo de Medio de Pago</th>
            <th>Código</th>
            <th>N° de Documento</th>
            <th>Empresa del Sistema financiero</th>
            <th>N° de Operación</th>
            <th>Fecha de Emisión del documento</th>
        </tr>
        <tr>
            <td>1</td>
            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="9">&nbsp;</td>
        </tr>
        <tr>
            <td class="bold">TOTAL</td>
            <td></td>
            <td class="bold">Dólares</td>
            <td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
    </table>

    <p class="mt-4">La transacción mencionada cancela en su totalidad a la Factura Nº ....................................</p>
    <p>De conformidad con lo expresado, firma al pie el presente.</p>
    <p>Atentamente,</p>

    <table class="tabla-firmas">
        @php $gruposFirmas = array_chunk($datos['copropiedad']['lista'], 2); @endphp
        @foreach($gruposFirmas as $grupo)
            <tr>
                @if(count($grupo) == 2)
                    <td style="width: 50%;">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[0]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[0]['tipo_doc'] }} N° {{ $grupo[0]['dni'] }}</p>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[1]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[1]['tipo_doc'] }} N° {{ $grupo[1]['dni'] }}</p>
                        </div>
                    </td>
                @else
                    <td colspan="2">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[0]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[0]['tipo_doc'] }} N° {{ $grupo[0]['dni'] }}</p>
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
    </table>

    <div class="page-break"></div>

    <!-- ======================================================= -->
    <!-- HOJA 2: CARTA PODER (ZONA REGISTRAL)                    -->
    <!-- ======================================================= -->
    <h3 class="text-center">CARTA PODER</h3>
    
    <div class="text-right">
        <p>{{ $datos['ciudad'] }}, {{ $fecha }}.</p>
    </div>

    <p>Señores<br>
    <span class="bold">OFICINA REGISTRAL DE {{ $oficinaRegistral }} ({{ $zonaRegistral }})</span><br>
    {{ $datos['ciudad'] }}.-</p>

    <p>Muy señores nuestros.</p>
    
    <div style="text-align: justify">
        <p>Yo, {!! $cadenaCompradores !!}. Otorgo amplio poder a {!! $apoderadosRegistral !!}, para que en mi representación realice los trámites para la Inmatriculación del Vehículo con Nº de Chasis: {{ empty($datos['vehiculo']['serie_chasis']) ? '____________________________________' : $datos['vehiculo']['serie_chasis'] }} y Nº de Motor: {{ empty($datos['vehiculo']['motor']) ? '____________________________________' : $datos['vehiculo']['motor'] }}, así como para que suscriba el Formato de Inmatriculación, Formato Notarial de Cambio de Características, Declaración de Tipo de Uso y cualquier documento que sea necesario para tal fin.</p>
    </div>
    
    <div style="text-align: justify">
        <p>Asimismo, declaramos que {{ $cadenaEstadosCiviles }} con domicilio del titular en {{ strtoupper($datos['copropiedad']['domicilio']) }}.</p>
    </div>
    
    <p>Sin otro particular, quedamos de Uds.</p>
    <p>Atentamente,</p>

    <table class="tabla-firmas">
        @foreach($gruposFirmas as $grupo)
            <tr>
                @if(count($grupo) == 2)
                    <td style="width: 50%;">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[0]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[0]['tipo_doc'] }} N° {{ $grupo[0]['dni'] }}</p>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[1]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[1]['tipo_doc'] }} N° {{ $grupo[1]['dni'] }}</p>
                        </div>
                    </td>
                @else
                    <td colspan="2">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[0]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[0]['tipo_doc'] }} N° {{ $grupo[0]['dni'] }}</p>
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
    </table>

    <div style="margin-top: 40px; text-align: justify; font-size: 10px;">
        <p class="uppercase" style="text-align: justify;">CERTIFICO:<br>
        QUE LAS FIRMAS QUE ANTECEDEN CORRESPONDEN A {{ $cadenaNombresCertifico }}. SE LEGALIZAN LAS FIRMAS MAS NO EL CONTENIDO. ESTE DOCUMENTO NO HA SIDO REDACTADO EN LA NOTARIA ART.108 D.L 1049: EL NOTARIO NO ASUME RESPONSABILIDAD SOBRE EL CONTENIDO DEL DOCUMENTO.</p>
    </div>

    <div class="page-break"></div>

    <!-- ======================================================= -->
    <!-- HOJA 3: CARTA PODER (AAP)                               -->
    <!-- ======================================================= -->
    <h3 class="text-center">CARTA PODER</h3>
    
    <div class="text-right">
        <p>{{ $datos['ciudad'] }}, {{ $fecha }}.</p>
    </div>

    <p>Señores<br>
    <span class="bold">ASOCIACIÓN AUTOMOTRIZ DEL PERÚ</span><br>
    {{ $datos['ciudad'] }}.-</p>

    <p>Muy señores nuestros.</p>
    
    <div style="text-align: justify">
        <p>{!! $cadenaCompradores !!}; otorgo amplio poder a {!! $apoderadosAAP !!}, para que en mi representación realicen los trámites para el recojo de la placa N° .........................</p>
    </div>
    
    <p>Sin otro particular, quedamos de Uds.</p>
    <p>Atentamente,</p>

    <table class="tabla-firmas">
        @foreach($gruposFirmas as $grupo)
            <tr>
                @if(count($grupo) == 2)
                    <td style="width: 50%;">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[0]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[0]['tipo_doc'] }} N° {{ $grupo[0]['dni'] }}</p>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[1]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[1]['tipo_doc'] }} N° {{ $grupo[1]['dni'] }}</p>
                        </div>
                    </td>
                @else
                    <td colspan="2">
                        <div class="caja-firma-centro">
                            <p class="uppercase" style="margin: 0;">{{ $grupo[0]['nombre'] }}</p>
                            <p style="margin: 0;">{{ $grupo[0]['tipo_doc'] }} N° {{ $grupo[0]['dni'] }}</p>
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
    </table>

</body>
</html>