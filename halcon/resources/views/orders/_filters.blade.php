<div class="card">
    <form method="GET" action="{{ $action }}" class="filters" data-test="order-filters">
        <div>
            <label for="invoice_number">Invoice number</label>
            <input type="text" id="invoice_number" name="invoice_number" value="{{ $filters['invoice_number'] ?? '' }}" placeholder="INV-00051">
        </div>
        <div>
            <label for="customer_number">Customer number</label>
            <input type="text" id="customer_number" name="customer_number" value="{{ $filters['customer_number'] ?? '' }}" placeholder="CUS-12345">
        </div>
        <div>
            <label for="date">Order date</label>
            <input type="date" id="date" name="date" value="{{ $filters['date'] ?? '' }}">
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Search</button>
        @if (array_filter($filters))
            <a href="{{ $action }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
</div>
