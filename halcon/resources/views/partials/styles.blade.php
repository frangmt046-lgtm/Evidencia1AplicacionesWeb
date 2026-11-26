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
        margin: 0; font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background: var(--bg); color: var(--text); line-height: 1.5;
    }
    a { color: var(--navy-2); }

    /* Layout */
    .shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
    .sidebar {
        background: var(--navy); color: #fff; padding: 22px 16px;
        display: flex; flex-direction: column; gap: 22px; position: sticky; top: 0; height: 100vh;
    }
    .content { padding: 32px 36px; max-width: 1150px; width: 100%; }

    .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
    .brand > span:last-child { display: flex; flex-direction: column; }
    .brand-mark {
        width: 38px; height: 38px; border-radius: 8px; background: var(--amber);
        display: grid; place-items: center; color: var(--navy); font-weight: 800; font-size: 20px;
    }
    .brand-name { font-size: 20px; font-weight: 800; letter-spacing: 4px; line-height: 1.1; }
    .brand-tag { font-size: 12px; color: #cbd5e1; }

    .menu { display: flex; flex-direction: column; gap: 4px; flex: 1; }
    .menu a {
        color: #e2e8f0; text-decoration: none; padding: 10px 12px; border-radius: 8px; font-weight: 500;
    }
    .menu a:hover { background: var(--navy-2); }
    .menu a.active { background: var(--amber); color: var(--navy); font-weight: 700; }

    .sidebar-user { display: flex; gap: 10px; align-items: center; border-top: 1px solid var(--navy-2); padding-top: 16px; }
    .avatar {
        width: 40px; height: 40px; border-radius: 50%; background: var(--navy-2);
        display: grid; place-items: center; font-weight: 700;
    }
    .sidebar-name { font-weight: 600; font-size: 14px; }

    /* Role badges */
    .role-badge {
        display: inline-block; font-size: 12px; font-weight: 700; padding: 2px 10px;
        border-radius: 999px; background: #e5e7eb; color: #374151;
    }
    .role-administrator { background: #fde68a; color: #78350f; }
    .role-sales { background: #bfdbfe; color: #1e3a8a; }
    .role-purchasing { background: #ddd6fe; color: #4c1d95; }
    .role-warehouse { background: #bbf7d0; color: #14532d; }
    .role-route { background: #fecaca; color: #7f1d1d; }

    /* Generic */
    h1 { margin: 0 0 4px; font-size: 28px; }
    .subtitle { color: var(--muted); margin: 0 0 24px; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
    .card {
        background: var(--card); border-radius: 12px; padding: 22px;
        box-shadow: 0 10px 30px rgba(20, 33, 61, .06); margin-bottom: 20px;
    }
    .grid { display: grid; gap: 16px; }
    .grid-4 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .grid-modules { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }

    .stat { background: var(--card); border-radius: 12px; padding: 18px; border-left: 4px solid var(--amber); }
    .stat-value { font-size: 28px; font-weight: 800; }
    .stat-label { color: var(--muted); font-size: 14px; }

    .module-card {
        display: block; text-decoration: none; color: var(--text); background: var(--card);
        border: 1px solid var(--line); border-radius: 12px; padding: 20px; transition: .15s;
    }
    .module-card:hover { border-color: var(--amber); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(20,33,61,.08); }
    .module-title { font-weight: 700; font-size: 17px; margin: 6px 0 4px; }
    .module-desc { color: var(--muted); font-size: 14px; margin: 0; }

    .welcome {
        background: linear-gradient(160deg, var(--navy) 0%, var(--navy-2) 100%); color: #fff;
        border-radius: 14px; padding: 26px 28px; margin-bottom: 24px;
    }
    .welcome h1 { color: #fff; }
    .welcome p { margin: 6px 0 0; color: #cbd5e1; }

    /* Buttons */
    .btn {
        display: inline-block; font: inherit; font-weight: 600; cursor: pointer; border: 0;
        border-radius: 8px; padding: 10px 18px; text-decoration: none; font-size: 15px;
    }
    .btn-primary { background: var(--amber); color: var(--navy); }
    .btn-primary:hover { background: var(--amber-dark); }
    .btn-secondary { background: #e5e7eb; color: var(--text); }
    .btn-small { padding: 6px 12px; font-size: 13px; }
    .btn-danger { background: #fee2e2; color: var(--error); }
    .btn-success { background: #d1fae5; color: var(--ok); }
    .btn-logout { width: 100%; background: transparent; color: #fff; border: 1px solid #475569; }
    .btn-logout:hover { background: var(--navy-2); }

    /* Forms */
    label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
    input[type=text], input[type=email], input[type=password], select {
        width: 100%; padding: 11px 13px; font-size: 15px; border: 1px solid #d1d5db;
        border-radius: 8px; outline: none; background: #fff; font-family: inherit;
    }
    input:focus, select:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(245,158,11,.25); }
    .is-invalid { border-color: var(--error) !important; }
    .field-error { color: var(--error); font-size: 13px; margin-top: 4px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-actions { display: flex; gap: 10px; margin-top: 22px; }
    .checkbox { display: flex; align-items: center; gap: 8px; font-weight: 500; }
    .help { color: var(--muted); font-size: 13px; margin-top: 4px; }
    .filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: end; }
    .filters > div { min-width: 170px; }

    /* Tables */
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { text-align: left; color: var(--muted); font-weight: 600; padding: 10px; border-bottom: 2px solid var(--line); }
    td { padding: 10px; border-bottom: 1px solid var(--line); vertical-align: middle; }
    .table-wrap { overflow-x: auto; }
    .actions { display: flex; gap: 6px; }
    .status { font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; }
    .status-active { color: var(--ok); }
    .status-inactive { color: var(--error); }
    .muted { color: var(--muted); }

    /* Alerts */
    .alert { border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; border: 1px solid; }
    .alert-ok { background: #ecfdf5; color: var(--ok); border-color: #a7f3d0; }
    .alert-error { background: #fef2f2; color: var(--error); border-color: #fecaca; }
    .alert-info { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }

    /* Pagination (Laravel simple-tailwind markup fallback) */
    nav[role=navigation] { margin-top: 14px; font-size: 14px; }
    nav[role=navigation] svg { width: 18px; height: 18px; }

    @media (max-width: 860px) {
        .shell { grid-template-columns: 1fr; }
        .sidebar { position: static; height: auto; }
        .content { padding: 20px; }
        .form-grid { grid-template-columns: 1fr; }
    }
</style>
