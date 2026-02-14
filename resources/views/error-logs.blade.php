{{-- Laravel Terminal Package - Error Logs View --}}
@extends(config('terminal.layout', 'layouts.admin'))

@section('content')
<style>
    .error-logs-dashboard {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .logs-header {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 2rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .logs-header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }

    .logs-header p {
        margin: 0.5rem 0 0;
        opacity: 0.9;
    }

    .logs-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .logs-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .logs-card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .logs-card-body {
        padding: 1.5rem;
    }

    .error-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    .error-table thead {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
    }

    .error-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
    }

    .error-table th:first-child {
        border-radius: 0.5rem 0 0 0;
    }

    .error-table th:last-child {
        border-radius: 0 0.5rem 0 0;
    }

    .error-table tbody tr {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s;
    }

    .error-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .error-table td {
        padding: 1rem;
        vertical-align: top;
    }

    .error-type-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .error-type-ERROR {
        background: #fee2e2;
        color: #991b1b;
    }

    .error-type-WARNING {
        background: #fef3c7;
        color: #92400e;
    }

    .error-type-CRITICAL {
        background: #fce7f3;
        color: #831843;
    }

    .error-message {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.875rem;
        color: #1e293b;
        max-width: 500px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .error-file {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.8rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .error-timestamp {
        font-size: 0.8rem;
        color: #64748b;
        white-space: nowrap;
    }

    .expand-btn {
        padding: 0.35rem 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .expand-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .stack-trace {
        margin-top: 1rem;
        padding: 1rem;
        background: #1e1e1e;
        color: #d4d4d4;
        border-radius: 0.5rem;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 0.8rem;
        line-height: 1.6;
        max-height: 300px;
        overflow-y: auto;
        display: none;
    }

    .stack-trace.show {
        display: block;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-box {
        background: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        border-left: 4px solid;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
    }

    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .stat-box.stat-total {
        border-color: #3b82f6;
    }

    .stat-box.stat-errors {
        border-color: #ef4444;
    }

    .stat-box.stat-warnings {
        border-color: #f59e0b;
    }

    .stat-box.stat-size {
        border-color: #10b981;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .loading-spinner {
        text-align: center;
        padding: 3rem;
        color: #667eea;
    }

    .loading-spinner i {
        font-size: 3rem;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
</style>

<div class="error-logs-dashboard">
    <div class="container">
        {{-- Header --}}
        <div class="logs-header">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1><i class="fas fa-exclamation-triangle me-3"></i>Error Logs</h1>
                    <p>Monitor and manage application errors</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('terminal.index') }}" class="btn btn-light">
                        <i class="fas fa-terminal me-2"></i>Terminal
                    </a>
                    <button class="btn btn-light" onclick="refreshLogs()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                    <button class="btn btn-light" onclick="downloadLogs()">
                        <i class="fas fa-download me-2"></i>Download
                    </button>
                    <button class="btn btn-danger" onclick="clearLogs()">
                        <i class="fas fa-trash me-2"></i>Clear Logs
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="stats-grid">
            <div class="stat-box stat-total">
                <div class="stat-value" id="totalErrors">0</div>
                <div class="stat-label">Total Errors</div>
            </div>
            <div class="stat-box stat-errors">
                <div class="stat-value" id="errorCount">0</div>
                <div class="stat-label">Errors</div>
            </div>
            <div class="stat-box stat-warnings">
                <div class="stat-value" id="warningCount">0</div>
                <div class="stat-label">Warnings</div>
            </div>
            <div class="stat-box stat-size">
                <div class="stat-value" id="logFileSize">0 KB</div>
                <div class="stat-label">Log File Size</div>
            </div>
        </div>

        {{-- Error Logs Table --}}
        <div class="logs-card">
            <div class="logs-card-header">
                <h5><i class="fas fa-list"></i>Latest 20 Errors</h5>
                <span class="badge bg-danger" id="errorBadge">Loading...</span>
            </div>
            <div class="logs-card-body">
                <div id="logsContainer">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner"></i>
                        <p class="mt-3">Loading error logs...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Load logs on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadErrorLogs();
    });

    function loadErrorLogs() {
        fetch('{{ route("terminal.get-error-logs") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayErrorLogs(data);
                    updateStatistics(data);
                } else {
                    showError('Failed to load error logs');
                }
            })
            .catch(error => {
                showError('Error loading logs: ' + error.message);
            });
    }

    function displayErrorLogs(data) {
        const container = document.getElementById('logsContainer');
        const errors = data.errors || [];

        if (errors.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>No Errors Found</h3>
                    <p>Your application is running smoothly!</p>
                </div>
            `;
            return;
        }

        let html = `
            <table class="error-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Message</th>
                        <th>File & Line</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        errors.forEach((error, index) => {
            html += `
                <tr>
                    <td>
                        <span class="error-type-badge error-type-${error.type}">${error.type}</span>
                    </td>
                    <td>
                        <div class="error-message" title="${escapeHtml(error.message)}">
                            ${escapeHtml(error.message)}
                        </div>
                    </td>
                    <td>
                        <div class="error-file">
                            <i class="fas fa-file-code"></i>
                            <span>${error.file}:${error.line}</span>
                        </div>
                    </td>
                    <td>
                        <div class="error-timestamp">${formatTimestamp(error.timestamp)}</div>
                    </td>
                    <td>
                        <button class="expand-btn" onclick="toggleStackTrace(${index})">
                            <i class="fas fa-code"></i> Details
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="padding: 0;">
                        <div id="stackTrace${index}" class="stack-trace">
                            <strong>Full Error Message:</strong><br>
                            ${escapeHtml(error.message)}<br><br>
                            <strong>Stack Trace:</strong><br>
                            ${escapeHtml(error.stack_trace) || 'No stack trace available'}
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        container.innerHTML = html;
    }

    function updateStatistics(data) {
        const errors = data.errors || [];
        const errorCount = errors.filter(e => e.type === 'ERROR').length;
        const warningCount = errors.filter(e => e.type === 'WARNING').length;
        const fileSize = formatFileSize(data.file_size || 0);

        document.getElementById('totalErrors').textContent = errors.length;
        document.getElementById('errorCount').textContent = errorCount;
        document.getElementById('warningCount').textContent = warningCount;
        document.getElementById('logFileSize').textContent = fileSize;
        document.getElementById('errorBadge').textContent = errors.length + ' errors';
    }

    function toggleStackTrace(index) {
        const stackTrace = document.getElementById('stackTrace' + index);
        stackTrace.classList.toggle('show');
    }

    function refreshLogs() {
        document.getElementById('logsContainer').innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner"></i>
                <p class="mt-3">Refreshing error logs...</p>
            </div>
        `;
        loadErrorLogs();
    }

    function clearLogs() {
        if (!confirm('Are you sure you want to clear all log files? This action cannot be undone.')) {
            return;
        }

        fetch('{{ route("terminal.clear-logs") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadErrorLogs();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }

    function downloadLogs() {
        window.location.href = '{{ route("terminal.download-logs") }}';
    }

    function showError(message) {
        document.getElementById('logsContainer').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-circle text-danger"></i>
                <h3>Error</h3>
                <p>${message}</p>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTimestamp(timestamp) {
        try {
            const date = new Date(timestamp);
            return date.toLocaleString();
        } catch {
            return timestamp;
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }
</script>
@endsection
