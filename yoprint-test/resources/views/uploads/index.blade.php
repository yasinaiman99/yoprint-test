<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YoPrint CSV Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .upload-box {
            border: 2px dashed #999;
            background: #fff;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
        }
        table th, table td { vertical-align: middle; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">📤 YoPrint CSV Upload</h2>

        <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="upload-box mb-3">
                <p>Select file or drag & drop your CSV here</p>
                <input type="file" name="file" class="form-control w-50 mx-auto mb-2" required>
                <button class="btn btn-primary">Upload File</button>
            </div>
        </form>

        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 25%">Time</th>
                    <th style="width: 50%">File Name</th>
                    <th style="width: 25%">Status</th>
                </tr>
            </thead>
            <tbody id="upload-list">
                @foreach ($uploads as $upload)
                    <tr>
                        <td>
                            {{ $upload->created_at->format('Y-m-d h:i A') }}<br>
                            <small>({{ $upload->created_at->diffForHumans() }})</small>
                        </td>
                        <td>{{ $upload->filename }}</td>
                        <td>
                            @if ($upload->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif ($upload->status === 'processing')
                                <span class="badge bg-warning text-dark">Processing</span>
                            @elseif ($upload->status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Auto-refresh upload list every 3 seconds
        setInterval(() => {
            $.get("{{ route('uploads.status') }}", data => {
                let html = '';
                data.forEach(u => {
                    html += `
                        <tr>
                            <td>${u.created_at_formatted}<br><small>(${u.created_at_human})</small></td>
                            <td>${u.filename}</td>
                            <td><span class="badge ${getBadge(u.status)}">${u.status}</span></td>
                        </tr>
                    `;
                });
                $('#upload-list').html(html);
            });
        }, 3000);

        function getBadge(status) {
            switch(status) {
                case 'completed': return 'bg-success';
                case 'processing': return 'bg-warning text-dark';
                case 'failed': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }
    </script>
</body>
</html>
