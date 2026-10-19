<div>

    @if ($connectionError)
        <div class="alert alert-danger">{{ $connectionError }}</div>
    @endif

    @if ($actionError)
        <div class="alert alert-danger" wire:key="action-error">{{ $actionError }}</div>
    @endif

    @if ($resultSummary)
        <div class="card borderRadius mb-3" wire:key="result-summary">
            <div class="card-body">
                <h6 class="mb-2">Import Result</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($resultSummary as $res)
                        <li class="{{ $res['status'] === 'created' ? 'text-success' : ($res['status'] === 'skipped' ? 'text-muted' : 'text-danger') }}">
                            <strong>{{ $res['filename'] }}</strong> — {{ $res['message'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card borderRadius">
        <div class="card-body p-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div class="btn-group" role="group" wire:loading.attr="disabled">
                    <button type="button" class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}"
                        wire:click="$set('filter', 'unread')">Unread</button>
                    <!--<button type="button" class="btn btn-sm {{ $filter === 'read' ? 'btn-primary' : 'btn-outline-primary' }}"-->
                    <!--    wire:click="$set('filter', 'read')">Read</button>-->
                    <!--<button type="button" class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}"-->
                    <!--    wire:click="$set('filter', 'all')">All</button>-->
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        wire:click="selectAllVisible(@js(collect($rows)->pluck('key')->all()))"
                        @disabled(empty($rows))>Select all on page</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        wire:click="clearSelection" @disabled(empty($selected))>Clear selection</button>
                    <button type="button" class="btn btn-sm btn-warning"
                        wire:click="saveSelected" wire:loading.attr="disabled"
                        @disabled(empty($selected))>Upload Selected ({{ count($selected) }})</button>
                    <button type="button" class="btn btn-sm btn-success"
                        wire:click="saveAllVisible(@js(collect($rows)->pluck('key')->all()))"
                        wire:loading.attr="disabled"
                        @disabled(empty($rows))>Upload All (this page)</button>
                </div>
            </div>

            <x-loading-overlay>
            <div class="table-responsive">
                <table class="table tableStyle mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th>Subject</th>
                            <th>From</th>
                            <th>Date</th>
                            <th>Attachment</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $isImported = isset($imported[$row['message_id'] . '|' . $row['attachment_filename']]);
                            @endphp
                            <tr wire:key="row-{{ $row['key'] }}">
                                <td>
                                    <input type="checkbox" wire:model="selected" value="{{ $row['key'] }}" @disabled($isImported)>
                                </td>
                                <td>{{ Str::limit($row['subject'], 60) }}</td>
                                <td>
                                    {{ $row['from_name'] ?: $row['from'] }}
                                    @if ($row['from_name'])
                                        <br><span class="text-muted small">{{ $row['from'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $row['date'] ? $row['date']->format('d M Y, h:i A') : '—' }}</td>
                                <td>
                                    {{ $row['attachment_filename'] }}
                                    <br><span class="text-muted small">{{ number_format($row['attachment_size'] / 1024, 1) }} KB</span>
                                </td>
                                <td>
                                    <span class="badge {{ $row['seen'] ? 'bg-secondary' : 'bg-primary' }}">
                                        {{ $row['seen'] ? 'Read' : 'Unread' }}
                                    </span>
                                    @if ($isImported)
                                        <span class="badge bg-success">Already imported</span>
                                    @endif
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a class="btn btn-outline-primary btn-sm mx-1"
                                        href="{{ route('admin.resume-inbox.attachment', [$row['uid'], $row['attachment_index']]) }}"
                                        target="_blank">View</a>

                                    <button type="button" class="btn btn-outline-success btn-sm mx-1"
                                        wire:click="saveOne({{ $row['uid'] }}, {{ $row['attachment_index'] }})"
                                        wire:loading.attr="disabled"
                                        @disabled($isImported)>Save</button>

                                    <button type="button" class="btn btn-outline-danger btn-sm mx-1"
                                        wire:click="removeEmail({{ $row['uid'] }})"
                                        wire:confirm="Is email ko Trash me move kar dein?">Remove</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Koi resume attachment nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted small">{{ $total }} resume(s) found</span>
                <div class="btn-group" wire:loading.attr="disabled">
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="gotoPage({{ $page - 1 }})" @disabled($page <= 1)>Previous</button>
                    <span class="btn btn-sm btn-light disabled">Page {{ $page }} of {{ $totalPages }}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="gotoPage({{ $page + 1 }})" @disabled($page >= $totalPages)>Next</button>
                </div>
            </div>
            </x-loading-overlay>

        </div>
    </div>
</div>
