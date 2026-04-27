<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            size: {{ $pageSize['width'] }}mm {{ $pageSize['height'] }}mm;
        }

        @foreach ($fonts as $fontName => $fontPath)
            @font-face {
                font-family: "{{ $fontName }}";
                src: url("file://{{ $fontPath }}") format("truetype");
                font-weight: normal;
                font-style: normal;
            }
        @endforeach

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: {{ $pageSize['width'] }}mm;
            height: {{ $pageSize['height'] }}mm;
            margin: 0;
            padding: 0;
        }

        body {
            color: #111827;
            font-family: "CormorantGaramond", "DejaVu Serif", serif;
        }

        .certificate-page {
            position: relative;
            width: {{ $pageSize['width'] }}mm;
            height: {{ $pageSize['height'] }}mm;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <main class="certificate-page">
        @foreach ($fields as $field)
            @if ($field['type'] === 'image')
                <img src="{{ $field['content'] }}" alt="" style="{{ $field['style'] }}">
            @else
                <div style="{{ $field['style'] }}">
                    <div style="{{ $field['contentStyle'] ?? '' }}">{{ $field['content'] }}</div>
                </div>
            @endif
        @endforeach
    </main>
</body>
</html>
