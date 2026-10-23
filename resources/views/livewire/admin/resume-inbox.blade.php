@php
    $pendingKeys = collect($rows)
        ->reject(fn ($row) => isset($imported[$row['message_id'] . '|' . $row['attachment_filename']]))
        ->pluck('key')
        ->values()
        ->all();
@endphp

<div x-data="{
        running: false,
        done: 0,
        total: 0,
        counts: { created: 0, skipped: 0, error: 0 },
        async runBatch(keys) {
            if (this.running || !keys.length) return;
            this.running = true;
            this.done = 0;
            this.total = keys.length;
            this.counts = { created: 0, skipped: 0, error: 0 };
            const results = [];
            for (const key of keys) {
                let result;
                try {
                    result = await $wire.importKey(key);
                } catch (e) {
                    result = null;
                }
                if (!result || !result.status) {
                    result = { key, filename: key, status: 'error', message: 'Request failed (server error or timeout) — skipped.' };
                }
                results.push(result);
                this.counts[result.status === 'created' || result.status === 'skipped' ? result.status : 'error']++;
                this.done++;
            }
            await $wire.finishBatch(results);
            this.running = false;
        }
    }">

    {{-- x-if (not x-show): Bootstrap's d-flex is display:flex !important and would override x-show --}}
    <template x-if="running">
        <div class="alert alert-info d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <div>
                Uploading resume <strong x-text="Math.min(done + 1, total)"></strong> of <strong x-text="total"></strong>…
                <span class="ms-2 text-success" x-text="counts.created + ' uploaded'"></span>,
                <span class="text-muted" x-text="counts.skipped + ' skipped'"></span>,
                <span class="text-danger" x-text="counts.error + ' failed'"></span>
                <div class="small text-muted">Please keep this page open until the upload finishes.</div>
            </div>
        </div>
    </template>

    @if ($connectionError)
        <div class="alert alert-danger">{{ $connectionError }}</div>
    @endif

    @if ($actionError)
        <div class="alert alert-danger" wire:key="action-error">{{ $actionError }}</div>
    @endif

    @if ($resultSummary)
        @php
            $summaryCounts = collect($resultSummary)->countBy('status');
        @endphp
        <div class="card borderRadius mb-3" wire:key="result-summary">
            <div class="card-body">
                <h6 class="mb-2">Import Result</h6>
                <p class="mb-2">
                    <span class="badge bg-success">{{ $summaryCounts->get('created', 0) }} uploaded</span>
                    <span class="badge bg-secondary">{{ $summaryCounts->get('skipped', 0) }} skipped (already imported / duplicate)</span>
                    <span class="badge bg-danger">{{ $summaryCounts->get('error', 0) }} failed</span>
                    <span class="text-muted small ms-1">out of {{ count($resultSummary) }} resume(s)</span>
                </p>
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
                <div class="btn-group" role="group" wire:loading.attr="disabled" :disabled="running">
                    <button type="button" class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' }}"
                        wire:click="$set('filter', 'unread')">Unread</button>
                    <!--<button type="button" class="btn btn-sm {{ $filter === 'read' ? 'btn-primary' : 'btn-outline-primary' }}"-->
                    <!--    wire:click="$set('filter', 'read')">Read</button>-->
                    <!--<button type="button" class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}"-->
                    <!--    wire:click="$set('filter', 'all')">All</button>-->
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        wire:click="refreshInbox" wire:loading.attr="disabled" :disabled="running">Refresh</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        wire:click="selectAllVisible(@js($pendingKeys))"
                        @disabled(empty($pendingKeys))>Select all on page</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                        wire:click="clearSelection" :disabled="running || !$wire.selected.length">Clear selection</button>
                    <button type="button" class="btn btn-sm btn-warning"
                        x-on:click="runBatch([...$wire.selected])" wire:loading.attr="disabled"
                        :disabled="running || !$wire.selected.length">Upload Selected (<span x-text="$wire.selected.length">{{ count($selected) }}</span>)</button>
                    <button type="button" class="btn btn-sm btn-success"
                        x-on:click="runBatch(@js($pendingKeys))"
                        wire:loading.attr="disabled"
                        :disabled="running || {{ empty($pendingKeys) ? 'true' : 'false' }}">Upload All (this page)</button>
                </div>
            </div>

            <x-loading-overlay target="filter,gotoPage,refreshInbox,saveOne,removeEmail,selectAllVisible,clearSelection,finishBatch">
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
                                        wire:loading.attr="disabled" :disabled="running || {{ $isImported ? 'true' : 'false' }}">Save</button>

                                    <button type="button" class="btn btn-outline-danger btn-sm mx-1"
                                        wire:click="removeEmail({{ $row['uid'] }})"
                                        wire:confirm="Move this email to Trash?">Remove</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No resume attachments found.
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
