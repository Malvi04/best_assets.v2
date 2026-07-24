<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; text-align: center; vertical-align: middle;">
        <thead>
            <tr>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Bulan</th>
                <th style="font-weight: bold; text-align: left; vertical-align: middle;">Nama Barang</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Total</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Total barang</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">Tgl pengambilan</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle;">PIC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedData as $monthName => $monthData)
                @php $isFirstRowMonth = true; @endphp
                @foreach($monthData['dates'] as $dateData)
                    @php $isFirstRowDate = true; @endphp
                    @foreach($dateData['assets'] as $assetName => $qty)
                        <tr>
                            @if($isFirstRowMonth)
                                <td rowspan="{{ $monthData['items'] }}" style="vertical-align: middle; text-align: center; font-weight: bold;">
                                    {{ $monthName }}
                                </td>
                            @endif
                            
                            <td style="text-align: left; vertical-align: middle;">
                                {{ $assetName }}
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                {{ $qty }}
                            </td>
                            
                            @if($isFirstRowMonth)
                                <td rowspan="{{ $monthData['items'] }}" style="vertical-align: middle; text-align: center;">
                                    {{ str_replace('.', ',', $monthData['total_qty']) }}
                                </td>
                            @endif
                            
                            @if($isFirstRowDate)
                                <td rowspan="{{ $dateData['items'] }}" style="vertical-align: middle; text-align: center;">
                                    {{ $dateData['date'] }}
                                </td>
                                <td rowspan="{{ $dateData['items'] }}" style="vertical-align: middle; text-align: center;">
                                    {{ $dateData['pic'] }}
                                </td>
                            @endif
                        </tr>
                        @php 
                            $isFirstRowMonth = false; 
                            $isFirstRowDate = false;
                        @endphp
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
