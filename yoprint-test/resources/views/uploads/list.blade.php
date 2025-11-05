@if (count($uploads))
    <ul class="list-group w-50">
        @foreach ($uploads as $upload)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $upload->filename }}</span>
                <span class="badge bg-{{ $upload->status === 'completed' ? 'success' : 'warning' }}">
                    {{ ucfirst($upload->status) }}
                </span>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted">No uploads yet.</p>
@endif
