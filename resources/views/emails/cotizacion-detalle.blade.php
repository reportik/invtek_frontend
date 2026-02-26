<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Cotización</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 650px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        {{-- Header --}}
        <tr>
            <td style="background-color: #59981A; padding: 20px 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 22px;">{{ $empresa }}</h1>
                <p style="color: #d4edaa; margin: 5px 0 0; font-size: 16px;">Detalle de Cotización #{{ $id_cotizacion_cliente }}</p>
            </td>
        </tr>

        {{-- Info general --}}
        <tr>
            <td style="padding: 25px 30px 10px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding-bottom: 8px;">
                            <strong style="color: #59981A;">Cliente:</strong> {{ $nombre_cliente }}
                            @if($email_cliente) ({{ $email_cliente }}) @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 8px;">
                            <strong style="color: #59981A;">Proyecto:</strong> {{ $nombre_proyecto }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 8px;">
                            <strong style="color: #59981A;">Artículo:</strong> {{ $nombre_articulo }}
                        </td>
                    </tr>
                    @if($nombre_tela)
                    <tr>
                        <td style="padding-bottom: 8px;">
                            <strong style="color: #59981A;">Tela:</strong> {{ $nombre_tela }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding-bottom: 8px;">
                            <strong style="color: #59981A;">Cot. Cliente:</strong> #{{ $id_cotizacion_cliente }}
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            <strong style="color: #59981A;">Cot. Interna:</strong> #{{ $id_cotizacion_interna }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Opciones seleccionadas --}}
        @if(!empty($opciones_seleccionadas))
        <tr>
            <td style="padding: 10px 30px;">
                <h3 style="color: #59981A; margin: 0 0 10px; font-size: 16px; border-bottom: 2px solid #59981A; padding-bottom: 5px;">Configuración</h3>
                <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 13px;">
                    @foreach($opciones_seleccionadas as $opcion)
                    <tr>
                        <td style="color: #666; width: 40%;">{{ $opcion['categoria'] }}</td>
                        <td style="font-weight: bold;">{{ $opcion['valor'] }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif

        {{-- Medidas --}}
        @if(!empty($medidas))
        <tr>
            <td style="padding: 10px 30px;">
                <h3 style="color: #59981A; margin: 0 0 10px; font-size: 16px; border-bottom: 2px solid #59981A; padding-bottom: 5px;">Medidas</h3>
                <table width="100%" cellpadding="5" cellspacing="0" style="font-size: 13px;">
                    @foreach($medidas as $medida)
                    <tr>
                        <td style="color: #666; width: 40%;">{{ $medida['label'] }}</td>
                        <td style="font-weight: bold;">{{ $medida['valor'] }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif

        {{-- Descripción --}}
        @if(!empty($descripcion_cortina))
        <tr>
            <td style="padding: 10px 30px;">
                <h3 style="color: #59981A; margin: 0 0 10px; font-size: 16px; border-bottom: 2px solid #59981A; padding-bottom: 5px;">Descripción</h3>
                <p style="font-size: 13px; color: #444; line-height: 1.5; margin: 0;">{{ $descripcion_cortina }}</p>
                @if(!empty($descripcion_cortinero))
                <p style="font-size: 13px; color: #444; line-height: 1.5; margin: 8px 0 0;">{{ $descripcion_cortinero }}</p>
                @endif
            </td>
        </tr>
        @endif

        {{-- Tabla de productos --}}
        <tr>
            <td style="padding: 15px 30px;">
                <h3 style="color: #59981A; margin: 0 0 10px; font-size: 16px; border-bottom: 2px solid #59981A; padding-bottom: 5px;">Productos</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 13px; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #59981A; color: #fff;">
                            <th style="text-align: left; padding: 8px;">Producto</th>
                            <th style="text-align: right; padding: 8px;">Cant.</th>
                            <th style="text-align: right; padding: 8px;">P. Unit.</th>
                            <th style="text-align: right; padding: 8px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 8px;">{{ $producto['nombre'] }}</td>
                            <td style="padding: 8px; text-align: right;">{{ $producto['cantidad'] }}</td>
                            <td style="padding: 8px; text-align: right;">${{ $producto['precio_unitario'] }}</td>
                            <td style="padding: 8px; text-align: right;">${{ $producto['total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>

        {{-- Totales --}}
        <tr>
            <td style="padding: 0 30px 20px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px;">
                    <tr>
                        <td style="text-align: right; padding: 5px 8px; color: #666;">Subtotal:</td>
                        <td style="text-align: right; padding: 5px 8px; width: 120px; font-weight: bold;">${{ $subtotal }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 5px 8px; color: #666;">IVA (16%):</td>
                        <td style="text-align: right; padding: 5px 8px; width: 120px; font-weight: bold;">${{ $iva }}</td>
                    </tr>
                    <tr style="background-color: #59981A; color: #fff;">
                        <td style="text-align: right; padding: 8px; font-size: 16px; font-weight: bold; border-radius: 4px 0 0 4px;">Total:</td>
                        <td style="text-align: right; padding: 8px; width: 120px; font-size: 16px; font-weight: bold; border-radius: 0 4px 4px 0;">${{ $total }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="background-color: #333; padding: 15px 30px; text-align: center;">
                <p style="color: #aaa; margin: 0; font-size: 12px;">
                    Este correo fue generado automáticamente por {{ $empresa }}, por favor no responder.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
