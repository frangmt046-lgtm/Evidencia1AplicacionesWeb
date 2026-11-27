@php
    $can = fn (string $field) => in_array($field, $editable, true);
    $customer = $order->customer;
@endphp

<h2 class="section-title">Order</h2>
<div class="form-grid">
    <div>
        <label for="invoice_number">Invoice number</label>
        <input type="text" id="invoice_number" value="{{ $order->exists ? $order->invoice_number : $nextInvoice }}" disabled>
        <div class="help">{{ $order->exists ? 'The invoice number cannot be changed.' : 'Assigned automatically (consecutive).' }}</div>
    </div>

    <div>
        <label for="status">Status</label>
        @if ($can('status'))
            <select id="status" name="status" class="{{ $errors->has('status') ? 'is-invalid' : '' }}">
                <option value="{{ $order->status }}">{{ $order->status }} (current)</option>
                @foreach ($allowedStatuses as $status)
                    <option value="{{ $status }}" @selected(old('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            @error('status') <div class="field-error">{{ $message }}</div> @enderror
        @else
            <input type="text" id="status" value="{{ $order->status }}" disabled>
            @unless ($order->exists)
                <div class="help">Every new order starts as Ordered.</div>
            @endunless
        @endif
    </div>

    <div>
        <label for="ordered_at">Date and time of the order</label>
        <input type="datetime-local" id="ordered_at" name="ordered_at"
               value="{{ old('ordered_at', $order->ordered_at?->format('Y-m-d\TH:i')) }}"
               class="{{ $errors->has('ordered_at') ? 'is-invalid' : '' }}" @disabled(! $can('ordered_at'))>
        @error('ordered_at') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="delivery_address">Delivery address</label>
        <input type="text" id="delivery_address" name="delivery_address"
               value="{{ old('delivery_address', $order->delivery_address) }}" placeholder="Street, number, city"
               class="{{ $errors->has('delivery_address') ? 'is-invalid' : '' }}" @disabled(! $can('delivery_address'))>
        @error('delivery_address') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label for="notes">Notes or additional information</label>
        <textarea id="notes" name="notes" rows="3" class="{{ $errors->has('notes') ? 'is-invalid' : '' }}"
                  @disabled(! $can('notes'))>{{ old('notes', $order->notes) }}</textarea>
        @error('notes') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<h2 class="section-title" style="margin-top:26px">Customer</h2>
@if ($can('customer'))
    <p class="help" style="margin-top:-8px">Type an existing customer number to load its data, or a new one to register the customer. Changes to the customer data apply to all of their orders.</p>
@endif
<div class="form-grid">
    <div>
        <label for="customer_number">Customer number</label>
        <input type="text" id="customer_number" name="customer_number" list="customer-list"
               value="{{ old('customer_number', $customer?->customer_number) }}" placeholder="CUS-12345"
               class="{{ $errors->has('customer_number') ? 'is-invalid' : '' }}" @disabled(! $can('customer'))>
        @error('customer_number') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="customer_name">Customer or company name</label>
        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $customer?->name) }}"
               class="{{ $errors->has('customer_name') ? 'is-invalid' : '' }}" @disabled(! $can('customer'))>
        @error('customer_name') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="tax_id">RFC (tax ID)</label>
        <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id', $customer?->tax_id) }}" placeholder="ABCD010203XYZ"
               class="{{ $errors->has('tax_id') ? 'is-invalid' : '' }}" @disabled(! $can('customer'))>
        @error('tax_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="fiscal_address">Fiscal address</label>
        <input type="text" id="fiscal_address" name="fiscal_address" value="{{ old('fiscal_address', $customer?->fiscal_address) }}"
               class="{{ $errors->has('fiscal_address') ? 'is-invalid' : '' }}" @disabled(! $can('customer'))>
        @error('fiscal_address') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $customer?->email) }}"
               class="{{ $errors->has('email') ? 'is-invalid' : '' }}" @disabled(! $can('customer'))>
        @error('email') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $customer?->phone) }}"
               class="{{ $errors->has('phone') ? 'is-invalid' : '' }}" @disabled(! $can('customer'))>
        @error('phone') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

@if ($can('customer'))
    <datalist id="customer-list">
        @foreach ($customers as $item)
            <option value="{{ $item->customer_number }}">{{ $item->name }}</option>
        @endforeach
    </datalist>

    <script>
        (() => {
            const customers = @json($customers);
            const fields = { customer_name: 'name', tax_id: 'tax_id', fiscal_address: 'fiscal_address', email: 'email', phone: 'phone' };
            document.getElementById('customer_number').addEventListener('change', (event) => {
                const customer = customers[event.target.value.trim().toUpperCase()];
                if (!customer) return;
                Object.entries(fields).forEach(([input, key]) => {
                    document.getElementById(input).value = customer[key] ?? '';
                });
            });
        })();
    </script>
@endif
