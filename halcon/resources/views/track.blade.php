<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HALCON | Track Your Order</title>
    <style>
        :root {
            --navy: #14213d;
            --navy-2: #1f3260;
            --amber: #f59e0b;
            --amber-dark: #d97706;
            --bg: #f3f4f6;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --ok: #059669;
            --error: #b91c1c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }
        [v-cloak] { display: none; }

        /* Header */
        .topbar { background: var(--navy); color: #fff; }
        .topbar .inner {
            max-width: 960px; margin: 0 auto; padding: 16px 20px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
        .brand-mark {
            width: 38px; height: 38px; border-radius: 8px; background: var(--amber);
            display: grid; place-items: center; color: var(--navy); font-weight: 800; font-size: 20px;
        }
        .brand-name { font-size: 24px; font-weight: 800; letter-spacing: 4px; }
        .brand-tag { font-size: 13px; color: #cbd5e1; }

        /* Hero + search card */
        .hero { background: linear-gradient(160deg, var(--navy) 0%, var(--navy-2) 100%); padding: 40px 20px 110px; color: #fff; }
        .hero .inner { max-width: 960px; margin: 0 auto; }
        .hero h1 { margin: 0 0 8px; font-size: 34px; }
        .hero p { margin: 0; color: #cbd5e1; max-width: 620px; }

        main { max-width: 960px; margin: -80px auto 40px; padding: 0 20px; }
        .card {
            background: var(--card); border-radius: 12px; padding: 24px;
            box-shadow: 0 10px 30px rgba(20, 33, 61, .08); margin-bottom: 20px;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: end; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
        input {
            width: 100%; padding: 12px 14px; font-size: 16px; border: 1px solid #d1d5db;
            border-radius: 8px; outline: none; text-transform: uppercase;
        }
        input::placeholder { text-transform: none; color: #9ca3af; }
        input:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(245, 158, 11, .25); }
        input.invalid { border-color: var(--error); }
        .field-error { color: var(--error); font-size: 13px; margin-top: 4px; min-height: 18px; }
        .hint { color: var(--muted); font-size: 13px; margin: 14px 0 0; }
        button {
            font: inherit; cursor: pointer; border: 0; border-radius: 8px;
        }
        .btn-primary {
            background: var(--amber); color: var(--navy); font-weight: 700; padding: 12px 22px;
            font-size: 16px; white-space: nowrap; margin-bottom: 22px;
        }
        .btn-primary:hover { background: var(--amber-dark); }
        .btn-primary:disabled { opacity: .6; cursor: wait; }
        .btn-link { background: none; color: var(--navy-2); font-weight: 600; padding: 0; text-decoration: underline; }

        .alert {
            border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;
            background: #fef2f2; color: var(--error); border: 1px solid #fecaca;
        }

        /* Result */
        .result-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
        .result-head h2 { margin: 0; font-size: 22px; }
        .muted { color: var(--muted); }
        .badge {
            display: inline-block; padding: 6px 14px; border-radius: 999px; font-weight: 700; font-size: 14px;
        }
        .badge-ordered    { background: #e0e7ff; color: #3730a3; }
        .badge-in-process { background: #fef3c7; color: #92400e; }
        .badge-in-route   { background: #dbeafe; color: #1e40af; }
        .badge-delivered  { background: #d1fae5; color: #065f46; }

        /* Progress steps */
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); margin: 28px 0 8px; position: relative; }
        .step { text-align: center; position: relative; }
        .step::before {
            content: ""; position: absolute; top: 17px; left: -50%; width: 100%; height: 4px; background: var(--line); z-index: 0;
        }
        .step:first-child::before { display: none; }
        .step.done::before { background: var(--amber); }
        .dot {
            position: relative; z-index: 1; width: 38px; height: 38px; border-radius: 50%; margin: 0 auto 8px;
            display: grid; place-items: center; font-weight: 700; background: var(--line); color: var(--muted);
        }
        .step.done .dot { background: var(--amber); color: var(--navy); }
        .step.current .dot { box-shadow: 0 0 0 5px rgba(245, 158, 11, .3); }
        .step-label { font-size: 14px; font-weight: 600; }
        .step-date { font-size: 12px; color: var(--muted); }

        .details { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; margin-top: 8px; }
        .details dt { font-size: 13px; color: var(--muted); }
        .details dd { margin: 2px 0 0; font-weight: 600; }

        .history { list-style: none; padding: 0; margin: 0; }
        .history li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--line); }
        .history li:last-child { border-bottom: 0; }

        .photo { max-width: 100%; border-radius: 8px; border: 1px solid var(--line); }
        .photo-missing { background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; padding: 18px; color: var(--muted); }

        h3 { margin: 0 0 12px; font-size: 17px; }
        footer { text-align: center; color: var(--muted); font-size: 13px; padding: 10px 20px 30px; }

        @media (max-width: 720px) {
            .form-grid, .details { grid-template-columns: 1fr; }
            .btn-primary { width: 100%; }
            .hero h1 { font-size: 28px; }
            .step-label { font-size: 12px; }
            .step-date { font-size: 10px; }
            .brand-tag { display: none; }
        }
    </style>
</head>
<body>
@verbatim
<div id="app" v-cloak>
    <header class="topbar">
        <div class="inner">
            <a class="brand" href="/">
                <span class="brand-mark">H</span>
                <span class="brand-name">HALCON</span>
            </a>
            <span class="brand-tag">Construction materials distributor</span>
        </div>
    </header>

    <section class="hero">
        <div class="inner">
            <h1>Track Your Order</h1>
            <p>Enter your Customer Number and the Invoice Number of your purchase, then click
               <strong>Check Order Status</strong> to see where your order is.</p>
        </div>
    </section>

    <main>
        <!-- Search form -->
        <form class="card" @submit.prevent="checkStatus" novalidate>
            <div class="form-grid">
                <div>
                    <label for="customer_number">Customer Number</label>
                    <input id="customer_number" v-model.trim="form.customer_number"
                           :class="{ invalid: errors.customer_number }"
                           placeholder="e.g. CUS-12345" autocomplete="off">
                    <div class="field-error">{{ errors.customer_number }}</div>
                </div>
                <div>
                    <label for="invoice_number">Invoice Number</label>
                    <input id="invoice_number" v-model.trim="form.invoice_number"
                           :class="{ invalid: errors.invoice_number }"
                           placeholder="e.g. INV-00001" autocomplete="off">
                    <div class="field-error">{{ errors.invoice_number }}</div>
                </div>
                <button type="submit" class="btn-primary" :disabled="loading">
                    {{ loading ? 'Checking…' : 'Check Order Status' }}
                </button>
            </div>
            <p class="hint">You can find both numbers on the invoice you received when you placed your order.</p>
        </form>

        <!-- Error message -->
        <div v-if="errorMessage" class="alert" role="alert">{{ errorMessage }}</div>

        <!-- Order information -->
        <template v-if="order">
            <section class="card">
                <div class="result-head">
                    <div>
                        <div class="muted">Invoice</div>
                        <h2>{{ order.invoice_number }}</h2>
                    </div>
                    <div style="text-align:right">
                        <div class="muted">Current status</div>
                        <span class="badge" :class="badgeClass(order.status)">{{ order.status }}</span>
                    </div>
                </div>

                <div class="steps">
                    <div v-for="(status, index) in order.statuses" :key="status" class="step"
                         :class="{ done: index < order.status_step, current: index + 1 === order.status_step }">
                        <div class="dot">{{ index < order.status_step ? '✓' : index + 1 }}</div>
                        <div class="step-label">{{ status }}</div>
                        <div class="step-date">{{ dateFor(status) }}</div>
                    </div>
                </div>
                <p class="muted" style="text-align:center; margin: 12px 0 0;">{{ statusMessage }}</p>
            </section>

            <section class="card">
                <h3>Order details</h3>
                <dl class="details">
                    <div><dt>Customer</dt><dd>{{ order.customer.name }} ({{ order.customer.customer_number }})</dd></div>
                    <div><dt>Order date</dt><dd>{{ formatDate(order.ordered_at) }}</dd></div>
                    <div><dt>Delivery address</dt><dd>{{ order.delivery_address }}</dd></div>
                    <div><dt>Last update</dt><dd>{{ formatDate(order.last_update) }}</dd></div>
                    <div v-if="order.notes" style="grid-column: 1 / -1"><dt>Notes</dt><dd>{{ order.notes }}</dd></div>
                </dl>
            </section>

            <section class="card">
                <h3>Status history</h3>
                <ul class="history">
                    <li v-for="(item, index) in order.history" :key="index">
                        <span class="badge" :class="badgeClass(item.status)">{{ item.status }}</span>
                        <span class="muted">{{ formatDate(item.changed_at) }}</span>
                    </li>
                </ul>
            </section>

            <section v-if="order.status === 'Delivered'" class="card">
                <h3>Proof of delivery</h3>
                <template v-if="order.delivery_photo">
                    <img v-if="!photoFailed" :src="order.delivery_photo.url" alt="Delivery photo"
                         class="photo" @error="photoFailed = true">
                    <div v-else class="photo-missing">
                        A delivery photo was registered on {{ formatDate(order.delivery_photo.uploaded_at) }},
                        but the image is not available right now.
                    </div>
                </template>
                <div v-else class="photo-missing">The delivery photo has not been uploaded yet.</div>
            </section>

            <p style="text-align:center"><button type="button" class="btn-link" @click="reset">Search another order</button></p>
        </template>
    </main>

    <footer>© {{ year }} HALCON · Order Tracking System</footer>
</div>
@endverbatim

<script src="{{ asset('js/vue.global.prod.js') }}"></script>
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                form: { customer_number: '', invoice_number: '' },
                errors: {},
                errorMessage: '',
                loading: false,
                order: null,
                photoFailed: false,
                year: new Date().getFullYear(),
            };
        },

        computed: {
            statusMessage() {
                const messages = {
                    'Ordered': 'We received your order and it is waiting to be prepared.',
                    'In process': 'Our warehouse is preparing your order.',
                    'In route': 'Your order is on its way to the delivery address.',
                    'Delivered': 'Your order was delivered. Thank you for buying with HALCON!',
                };
                return messages[this.order.status] || '';
            },
        },

        methods: {
            validate() {
                this.errors = {};
                if (!this.form.customer_number) this.errors.customer_number = 'Please enter your Customer Number.';
                if (!this.form.invoice_number) this.errors.invoice_number = 'Please enter your Invoice Number.';
                return Object.keys(this.errors).length === 0;
            },

            async checkStatus() {
                this.errorMessage = '';
                this.order = null;
                this.photoFailed = false;
                if (!this.validate()) return;

                this.loading = true;
                const params = new URLSearchParams({
                    customer_number: this.form.customer_number.toUpperCase(),
                    invoice_number: this.form.invoice_number.toUpperCase(),
                });

                try {
                    const response = await fetch(`/api/track?${params}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();

                    if (response.ok) {
                        this.order = data;
                    } else if (response.status === 422 && data.errors) {
                        // Validation errors returned by Laravel
                        for (const field in data.errors) this.errors[field] = data.errors[field][0];
                    } else {
                        this.errorMessage = data.message || 'The order could not be found.';
                    }
                } catch (error) {
                    this.errorMessage = 'We could not connect to the server. Please try again in a moment.';
                } finally {
                    this.loading = false;
                }
            },

            reset() {
                this.order = null;
                this.errorMessage = '';
                this.form = { customer_number: '', invoice_number: '' };
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            badgeClass(status) {
                return 'badge-' + status.toLowerCase().replace(/\s+/g, '-');
            },

            dateFor(status) {
                const item = this.order.history.find(h => h.status === status);
                return item ? this.formatDate(item.changed_at, false) : '';
            },

            formatDate(value, withTime = true) {
                if (!value) return '—';
                const options = withTime
                    ? { dateStyle: 'medium', timeStyle: 'short' }
                    : { dateStyle: 'medium' };
                return new Intl.DateTimeFormat('en-US', options).format(new Date(value));
            },
        },
    }).mount('#app');
</script>
</body>
</html>
