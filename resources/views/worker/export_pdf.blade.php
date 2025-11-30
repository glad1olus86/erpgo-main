<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Список работников') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header .date {
            font-size: 10px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 4px 3px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }
        
        td {
            font-size: 8px;
        }
        
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Список работников') }}</h1>
        <div class="date">{{ __('Дата генерации:') }} {{ $generatedAt }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>{{ __('Имя') }}</th>
                <th>{{ __('Фамилия') }}</th>
                <th>{{ __('Дата рожд.') }}</th>
                <th>{{ __('Возраст') }}</th>
                <th>{{ __('Пол') }}</th>
                <th>{{ __('Нац.') }}</th>
                <th>{{ __('Дата рег.') }}</th>
                <th>{{ __('Отель') }}</th>
                <th>{{ __('Комната') }}</th>
                <th>{{ __('Дата засел.') }}</th>
                <th>{{ __('Кем засел.') }}</th>
                <th>{{ __('Место работы') }}</th>
                <th>{{ __('Дата труд.') }}</th>
                <th>{{ __('Время раб.') }}</th>
                <th>{{ __('Кем устр.') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workers as $worker)
                <tr>
                    <td>{{ $worker['first_name'] }}</td>
                    <td>{{ $worker['last_name'] }}</td>
                    <td class="nowrap">{{ $worker['dob'] }}</td>
                    <td class="text-center">{{ $worker['age'] }}</td>
                    <td>{{ $worker['gender'] }}</td>
                    <td>{{ $worker['nationality'] }}</td>
                    <td class="nowrap">{{ $worker['registration_date'] }}</td>
                    <td>{{ $worker['hotel'] }}</td>
                    <td class="text-center">{{ $worker['room'] }}</td>
                    <td class="nowrap">{{ $worker['check_in_date'] }}</td>
                    <td>{{ $worker['checked_in_by'] }}</td>
                    <td>{{ $worker['work_place'] }}</td>
                    <td class="nowrap">{{ $worker['work_started_at'] }}</td>
                    <td class="nowrap">{{ $worker['work_duration'] }}</td>
                    <td>{{ $worker['work_assigned_by'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
