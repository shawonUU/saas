<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ t('Customers','কাস্টমার') }}</title>
    <style>
        @page{margin:14mm 11mm}
        @if(is_file(public_path('fonts/NotoSansBengali-Regular.ttf')))
        @font-face{font-family:'CustomerReportBn';src:url("file:///{{ ltrim(str_replace('\\','/',public_path('fonts/NotoSansBengali-Regular.ttf')), '/') }}") format('truetype')}
        @endif
        body{font-family:'CustomerReportBn','DejaVu Sans',sans-serif;color:#19253b;font-size:9px}
        h1{font-size:18px;margin:0 0 4px}
        .meta{color:#62738b;font-size:9px;margin-bottom:15px}
        table{width:100%;border-collapse:collapse;table-layout:fixed}
        th{background:#5547d9;color:#fff;padding:8px 6px;text-align:left;font-size:8px}
        td{border-bottom:1px solid #e1e6ef;padding:7px 6px;vertical-align:top;word-wrap:break-word;overflow-wrap:break-word}
        tr:nth-child(even) td{background:#f6f8fc}
        .empty{text-align:center;padding:22px;color:#65738c}
    </style>
</head>
<body>
    <h1>{{ $business->name ?? t('Customers','কাস্টমার') }}</h1>
    <div class="meta">{{ t('Customers Report','কাস্টমার রিপোর্ট') }} · {{ now()->format('d M Y, h:i A') }} · {{ t('Matching records','মোট রেকর্ড') }}: {{ $records->count() }}</div>
    <table>
        <thead><tr>
            <th style="width:5%">{{ t('ID','আইডি') }}</th><th style="width:10%">{{ t('Code','কোড') }}</th>
            <th style="width:15%">{{ t('Customer Name','কাস্টমারের নাম') }}</th>
            <th style="width:15%">{{ t('Bangla Name','বাংলা নাম') }}</th>
            <th style="width:12%">{{ t('Phone','ফোন') }}</th>
            <th style="width:17%">{{ t('Email','ইমেইল') }}</th>
            <th style="width:18%">{{ t('Address','ঠিকানা') }}</th>
            <th style="width:8%">{{ t('Status','অবস্থা') }}</th>
        </tr></thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->id }}</td><td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td><td>{{ $record->name_bn }}</td>
                    <td>{{ $record->phone }}</td><td>{{ $record->email }}</td>
                    <td>{{ $record->address }}</td>
                    <td>{{ $record->is_active ? t('Active','সক্রিয়') : t('Inactive','নিষ্ক্রিয়') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty">{{ t('No matching customers found','কোনো কাস্টমার পাওয়া যায়নি') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
