<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/DashboardShared.php';

DashboardEnsureSessionStarted();

$ErrorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Action = isset($_POST['Action']) ? strval($_POST['Action']) : '';

    if ($Action === 'logout') {
        DashboardLogout();
        header('Location: ./Dashboard.php');
        exit;
    }

    if ($Action === 'login') {
        $Password = isset($_POST['Password']) ? strval($_POST['Password']) : '';
        if (DashboardLogin($Password)) {
            header('Location: ./Dashboard.php');
            exit;
        }

        $ErrorMessage = 'Password not recognised.';
    }
}

$DashboardTitle = htmlspecialchars(DashboardGetTitle(), ENT_QUOTES, 'UTF-8');
$IsConfigured = DashboardIsAuthConfigured();
$IsAuthenticated = $IsConfigured && DashboardIsAuthenticated();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $DashboardTitle; ?></title>
    <style>
        :root {
            --bg-top: #10253a;
            --bg-bottom: #f4eadb;
            --panel: rgba(255, 249, 240, 0.9);
            --panel-strong: #fffdf8;
            --line: rgba(17, 34, 51, 0.12);
            --ink: #16222f;
            --muted: #5f6a74;
            --accent: #b45422;
            --accent-soft: #f2c57c;
            --good: #1f8f69;
            --warn: #c17f1a;
            --bad: #b6412e;
            --table-head: #1d3144;
            --shadow: 0 20px 55px rgba(12, 22, 35, 0.16);
            --radius: 22px;
            --font-sans: "Aptos", "Trebuchet MS", "Gill Sans", sans-serif;
            --font-serif: "Georgia", "Palatino Linotype", serif;
            --font-mono: "Consolas", "Courier New", monospace;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: var(--font-sans);
            background:
                radial-gradient(circle at top left, rgba(242, 197, 124, 0.26), transparent 32%),
                radial-gradient(circle at top right, rgba(180, 84, 34, 0.16), transparent 28%),
                linear-gradient(180deg, var(--bg-top) 0%, #1c3951 24%, #e9daca 24.1%, var(--bg-bottom) 100%);
        }

        a {
            color: inherit;
        }

        button,
        input,
        select {
            font: inherit;
        }

        .shell {
            width: min(1680px, calc(100vw - 32px));
            margin: 0 auto;
            padding: 24px 0 40px;
        }

        .hero,
        .panel,
        .auth-card {
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: var(--radius);
            background: var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .hero {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1.8fr) minmax(280px, 1fr);
            padding: 28px;
            color: #f9f5ef;
            background:
                linear-gradient(140deg, rgba(6, 20, 34, 0.9), rgba(22, 49, 68, 0.88)),
                linear-gradient(135deg, rgba(180, 84, 34, 0.22), transparent);
        }

        .eyebrow {
            margin: 0 0 10px;
            color: rgba(255, 241, 216, 0.8);
            letter-spacing: 0.22em;
            text-transform: uppercase;
            font-size: 0.78rem;
        }

        h1 {
            margin: 0 0 12px;
            font: 700 clamp(2rem, 5vw, 3.55rem) / 1.02 var(--font-serif);
            letter-spacing: -0.03em;
        }

        .hero p {
            margin: 0;
            max-width: 70ch;
            color: rgba(249, 245, 239, 0.88);
            line-height: 1.55;
        }

        .hero-meta {
            display: grid;
            align-content: space-between;
            gap: 14px;
        }

        .meta-stack {
            display: grid;
            gap: 12px;
        }

        .stamp {
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(250, 244, 235, 0.08);
            border: 1px solid rgba(250, 244, 235, 0.14);
        }

        .stamp-label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.78rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 241, 216, 0.7);
        }

        .stamp-value {
            font-size: 1.02rem;
            color: #fffaf4;
        }

        .logout-form {
            margin: 0;
        }

        .summary-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            margin-top: 20px;
        }

        .summary-card {
            padding: 18px;
            border-radius: 20px;
            background: var(--panel-strong);
            border: 1px solid rgba(18, 34, 49, 0.08);
            box-shadow: 0 12px 35px rgba(20, 32, 46, 0.08);
            overflow: hidden;
            position: relative;
        }

        .summary-card::after {
            content: "";
            position: absolute;
            inset: auto -20% -45% auto;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(242, 197, 124, 0.34), transparent 66%);
            pointer-events: none;
        }

        .summary-label {
            margin: 0 0 10px;
            color: var(--muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.76rem;
        }

        .summary-value {
            margin: 0;
            font: 700 clamp(1.8rem, 4vw, 2.8rem) / 1 var(--font-serif);
        }

        .summary-note {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .panel {
            margin-top: 20px;
            padding: 22px;
        }

        .panel-title {
            margin: 0 0 6px;
            font: 700 1.18rem / 1.2 var(--font-serif);
        }

        .panel-copy {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .controls-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            margin-top: 18px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label,
        .toggle-label {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .field input,
        .field select {
            width: 100%;
            min-height: 46px;
            padding: 11px 14px;
            border-radius: 14px;
            border: 1px solid rgba(19, 35, 49, 0.14);
            background: #fffdf9;
            color: var(--ink);
        }

        .actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-top: 18px;
        }

        .button {
            min-height: 46px;
            padding: 11px 16px;
            border: none;
            border-radius: 999px;
            color: #fff8ef;
            background: linear-gradient(135deg, var(--accent), #8a3511);
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(138, 53, 17, 0.24);
            transition: transform 160ms ease, box-shadow 160ms ease, opacity 160ms ease;
        }

        .button:hover,
        .button:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(138, 53, 17, 0.28);
        }

        .button.secondary {
            color: var(--ink);
            background: linear-gradient(135deg, #f8ecd9, #ebd1a0);
            box-shadow: 0 10px 25px rgba(151, 118, 42, 0.17);
        }

        .toggle {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(248, 236, 217, 0.7);
            border: 1px solid rgba(21, 37, 52, 0.08);
        }

        .legend-bar {
            height: 16px;
            margin-top: 16px;
            border-radius: 999px;
            background: linear-gradient(90deg,
                hsla(145, 78%, 48%, 0.28) 0%,
                hsla(96, 78%, 50%, 0.34) 20%,
                hsla(54, 86%, 56%, 0.42) 48%,
                hsla(22, 86%, 55%, 0.54) 74%,
                hsla(7, 86%, 54%, 0.68) 100%);
            border: 1px solid rgba(21, 37, 52, 0.1);
        }

        .legend-scale {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .state-breakdown {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .chip {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f9f2e7;
            border: 1px solid rgba(18, 34, 49, 0.08);
            font-size: 0.9rem;
        }

        .chip strong {
            font-family: var(--font-mono);
        }

        .table-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .table-meta strong {
            font-size: 1.05rem;
        }

        .table-wrap {
            overflow: auto;
            border-radius: 18px;
            border: 1px solid rgba(20, 36, 50, 0.08);
            background: rgba(255, 255, 255, 0.7);
        }

        table {
            width: 100%;
            min-width: 1760px;
            border-collapse: separate;
            border-spacing: 0;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: 0;
            background: var(--table-head);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        thead th:first-child {
            left: 0;
            z-index: 3;
        }

        .sort-button {
            width: 100%;
            padding: 14px 12px;
            border: none;
            color: #f6efe5;
            text-align: left;
            background: transparent;
            cursor: pointer;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .sort-button[data-active="true"] {
            color: #ffd89b;
        }

        tbody td {
            padding: 12px;
            border-bottom: 1px solid rgba(18, 34, 49, 0.08);
            vertical-align: top;
            background: rgba(255, 253, 248, 0.82);
            font-size: 0.94rem;
        }

        tbody td:first-child {
            position: sticky;
            left: 0;
            z-index: 1;
            background: #fffaf4;
            box-shadow: 10px 0 22px rgba(20, 32, 46, 0.05);
        }

        tbody tr:hover td {
            background: rgba(248, 238, 223, 0.92);
        }

        tbody tr:hover td:first-child {
            background: rgba(252, 244, 230, 0.98);
        }

        .mono {
            font-family: var(--font-mono);
            font-size: 0.88rem;
        }

        .participant-cell {
            min-width: 140px;
        }

        .participant-id {
            display: block;
            font-weight: 700;
        }

        .participant-link {
            display: inline-block;
            margin-top: 6px;
            color: var(--accent);
            text-decoration: none;
            font-size: 0.88rem;
        }

        .participant-link:hover,
        .participant-link:focus-visible {
            text-decoration: underline;
        }

        .state-pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.84rem;
            line-height: 1.1;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .state-pill[data-tone="active"] {
            color: #0e543f;
            background: rgba(31, 143, 105, 0.14);
            border-color: rgba(31, 143, 105, 0.18);
        }

        .state-pill[data-tone="pending"] {
            color: #7b4d0d;
            background: rgba(193, 127, 26, 0.14);
            border-color: rgba(193, 127, 26, 0.2);
        }

        .state-pill[data-tone="complete"] {
            color: #114465;
            background: rgba(37, 127, 201, 0.14);
            border-color: rgba(37, 127, 201, 0.2);
        }

        .state-pill[data-tone="excluded"] {
            color: #7e1f1f;
            background: rgba(182, 65, 46, 0.14);
            border-color: rgba(182, 65, 46, 0.22);
        }

        .state-pill[data-tone="unknown"] {
            color: #49545e;
            background: rgba(73, 84, 94, 0.12);
            border-color: rgba(73, 84, 94, 0.18);
        }

        .heat-cell {
            border-radius: 12px;
            font-weight: 700;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .muted {
            color: var(--muted);
        }

        .empty-state {
            padding: 18px;
            text-align: center;
            color: var(--muted);
        }

        .status-banner {
            min-height: 24px;
            margin-top: 12px;
            color: var(--muted);
        }

        .status-banner[data-tone="error"] {
            color: var(--bad);
        }

        .status-banner[data-tone="ok"] {
            color: var(--good);
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .auth-card {
            width: min(540px, 100%);
            padding: 28px;
            background:
                linear-gradient(155deg, rgba(255, 250, 243, 0.97), rgba(247, 237, 223, 0.95));
        }

        .auth-card h1 {
            color: var(--ink);
            font-size: clamp(2rem, 5vw, 2.9rem);
        }

        .auth-card p {
            color: var(--muted);
            line-height: 1.6;
        }

        .auth-card form {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .auth-card input[type="password"] {
            min-height: 48px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(19, 35, 49, 0.14);
            background: #fffdfa;
        }

        .error {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 14px;
            color: #7e1f1f;
            background: rgba(182, 65, 46, 0.1);
            border: 1px solid rgba(182, 65, 46, 0.18);
        }

        .reveal {
            animation: rise-in 520ms ease both;
        }

        @keyframes rise-in {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 980px) {
            .shell {
                width: min(100vw - 20px, 1680px);
                padding-top: 14px;
            }

            .hero {
                grid-template-columns: 1fr;
                padding: 22px;
            }

            .panel,
            .auth-card {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
<?php if (!$IsConfigured): ?>
    <div class="auth-shell">
        <section class="auth-card reveal">
            <p class="eyebrow">Dashboard Setup</p>
            <h1><?php echo $DashboardTitle; ?></h1>
            <p>
                The dashboard password hash is not configured yet. Add a SHA-256 hash to
                <span class="mono">Credentials.php</span> as
                <span class="mono">$DashboardPasswordSha256</span> and then reload this page.
            </p>
        </section>
    </div>
<?php elseif (!$IsAuthenticated): ?>
    <div class="auth-shell">
        <section class="auth-card reveal">
            <p class="eyebrow">Protected Access</p>
            <h1><?php echo $DashboardTitle; ?></h1>
            <p>
                This page exposes participant activity and registration data, so it is locked
                behind a shared dashboard password.
            </p>
            <form method="post" action="./Dashboard.php">
                <input type="hidden" name="Action" value="login">
                <label for="Password">Dashboard password</label>
                <input id="Password" name="Password" type="password" autocomplete="current-password" required>
                <button class="button" type="submit">Unlock dashboard</button>
            </form>
            <?php if ($ErrorMessage !== ''): ?>
                <div class="error"><?php echo htmlspecialchars($ErrorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </section>
    </div>
<?php else: ?>
    <div class="shell">
        <header class="hero reveal">
            <div>
                <p class="eyebrow">RepDist participant monitor</p>
                <h1><?php echo $DashboardTitle; ?></h1>
                <p>
                    A standalone monitoring view over the <span class="mono">Register</span> table,
                    enriched with server-side activity from relandings, unfocus warnings,
                    exclusions, instruction warnings, and feedback.
                </p>
            </div>
            <div class="hero-meta">
                <div class="meta-stack">
                    <div class="stamp">
                        <span class="stamp-label">Server time zone</span>
                        <span class="stamp-value">Europe/London</span>
                    </div>
                    <div class="stamp">
                        <span class="stamp-label">Last refresh</span>
                        <span class="stamp-value" id="generatedAtLabel">Waiting for first refresh...</span>
                    </div>
                    <div class="stamp">
                        <span class="stamp-label">Dashboard mode</span>
                        <span class="stamp-value">Live activity view with logarithmic idle heat map</span>
                    </div>
                </div>
                <form class="logout-form" method="post" action="./Dashboard.php">
                    <input type="hidden" name="Action" value="logout">
                    <button class="button secondary" type="submit">Lock dashboard</button>
                </form>
            </div>
        </header>

        <section class="summary-grid reveal" id="summaryGrid" aria-live="polite"></section>

        <section class="panel reveal">
            <h2 class="panel-title">Controls</h2>
            <p class="panel-copy">
                Search by participant fields, filter by state, and keep the page live. The
                inactivity colouring is logarithmic, so short gaps remain readable while long
                silent periods become progressively more urgent.
            </p>
            <div class="controls-grid">
                <div class="field">
                    <label for="searchInput">Search participants</label>
                    <input id="searchInput" type="search" placeholder="SubjectId, PoolId, GroupId, gender, language...">
                </div>
                <div class="field">
                    <label for="stateFilter">State filter</label>
                    <select id="stateFilter">
                        <option value="all">All states</option>
                    </select>
                </div>
                <div class="field">
                    <label for="idleFilter">Idle threshold</label>
                    <select id="idleFilter">
                        <option value="all">Any activity age</option>
                        <option value="3600">More than 1 hour</option>
                        <option value="86400">More than 24 hours</option>
                        <option value="604800">More than 7 days</option>
                    </select>
                </div>
            </div>
            <div class="actions-row">
                <button class="button" id="refreshButton" type="button">Refresh now</button>
                <label class="toggle" for="autoRefreshToggle">
                    <input id="autoRefreshToggle" type="checkbox" checked>
                    <span class="toggle-label">Auto refresh every 60 seconds</span>
                </label>
            </div>
            <div class="status-banner" id="statusBanner" data-tone="ok"></div>
        </section>

        <section class="panel reveal">
            <h2 class="panel-title">Heat Map Legend</h2>
            <p class="panel-copy">
                Fresh rows stay green, then move through amber to red as inactivity increases.
                The scale is logarithmic, so the visual jump from 1 minute to 10 minutes is
                comparable to the jump from 1 hour to 10 hours.
            </p>
            <div class="legend-bar" aria-hidden="true"></div>
            <div class="legend-scale">
                <span>Fresh</span>
                <span>Hours</span>
                <span>Days</span>
                <span>Dormant</span>
            </div>
            <div class="state-breakdown" id="stateBreakdown"></div>
        </section>

        <section class="panel reveal">
            <div class="table-meta">
                <strong id="rowCountLabel">Loading participants...</strong>
                <span class="muted" id="filterSummaryLabel">Preparing filters...</span>
            </div>
            <div class="table-wrap">
                <table aria-describedby="filterSummaryLabel">
                    <thead>
                        <tr>
                            <th><button class="sort-button" type="button" data-sort-key="SubjectId">Participant</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="PoolId">PoolId</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="State">State</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="StateLabel">Activity</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="LastServerUpdate">Last update</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="SecondsSinceLastUpdate">Since update</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="LastServerUpdateSource">Last source</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="RelandingCount">Relands</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="UnfocusCount">Unfocuses</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="NaughtyCount">Warnings</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="ExclusionCount">Exclusions</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="HasFeedback">Feedback</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="GroupId">GroupId</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="ClientTimeZone">Client TZ</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="BMY">BMY</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="Gender">Gender</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="Handedness">Handedness</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="L1">L1</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="DateTime_Landing">Landing</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="DateTime_Consent">Consent</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="DateTime_Register">Register</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="DateTime_TIinstr">TI instr</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="DateTime_TItrain">TI train</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="DateTime_TIprobe">TI probe</button></th>
                            <th><button class="sort-button" type="button" data-sort-key="ImgPerm">ImgPerm</button></th>
                        </tr>
                    </thead>
                    <tbody id="participantsBody">
                        <tr>
                            <td class="empty-state" colspan="25">Loading dashboard data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const state = {
                participants: [],
                summary: null,
                fetchedAtMs: 0,
                sortKey: 'LastServerUpdate',
                sortDir: 'desc',
                autoRefreshMs: 60000
            };

            const els = {
                generatedAtLabel: document.getElementById('generatedAtLabel'),
                summaryGrid: document.getElementById('summaryGrid'),
                stateBreakdown: document.getElementById('stateBreakdown'),
                searchInput: document.getElementById('searchInput'),
                stateFilter: document.getElementById('stateFilter'),
                idleFilter: document.getElementById('idleFilter'),
                refreshButton: document.getElementById('refreshButton'),
                autoRefreshToggle: document.getElementById('autoRefreshToggle'),
                statusBanner: document.getElementById('statusBanner'),
                rowCountLabel: document.getElementById('rowCountLabel'),
                filterSummaryLabel: document.getElementById('filterSummaryLabel'),
                participantsBody: document.getElementById('participantsBody'),
                sortButtons: Array.from(document.querySelectorAll('.sort-button'))
            };

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function truncateText(value, limit) {
                const text = String(value ?? '');
                if (text.length <= limit) {
                    return text;
                }

                return text.slice(0, limit - 3) + '...';
            }

            function formatTimestamp(value) {
                return value ? escapeHtml(value) : '<span class="muted">-</span>';
            }

            function currentSecondsSince(row) {
                if (row.SecondsSinceLastUpdate === null || row.SecondsSinceLastUpdate === undefined) {
                    return null;
                }

                const extra = Math.max(0, Math.floor((Date.now() - state.fetchedAtMs) / 1000));
                return row.SecondsSinceLastUpdate + extra;
            }

            function formatDuration(seconds) {
                if (seconds === null || Number.isNaN(seconds)) {
                    return '-';
                }

                if (seconds < 60) {
                    return '< 1m';
                }

                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) {
                    return `${minutes}m`;
                }

                const hours = Math.floor(minutes / 60);
                const remMinutes = minutes % 60;
                if (hours < 48) {
                    return remMinutes > 0 ? `${hours}h ${remMinutes}m` : `${hours}h`;
                }

                const days = Math.floor(hours / 24);
                const remHours = hours % 24;
                return remHours > 0 ? `${days}d ${remHours}h` : `${days}d`;
            }

            function getHeatStyle(seconds) {
                if (seconds === null || Number.isNaN(seconds)) {
                    return '';
                }

                const maxSeconds = 30 * 24 * 60 * 60;
                const ratio = Math.min(1, Math.log10(seconds + 1) / Math.log10(maxSeconds + 1));
                const hue = 145 - (138 * ratio);
                const alpha = 0.18 + (0.5 * ratio);
                const textColor = ratio > 0.66 ? '#33150e' : '#132230';
                return `background: hsla(${hue.toFixed(1)}, 85%, 54%, ${alpha.toFixed(3)}); color: ${textColor};`;
            }

            function numericValue(value) {
                if (value === null || value === '' || value === undefined) {
                    return null;
                }

                const parsed = Number(value);
                return Number.isNaN(parsed) ? null : parsed;
            }

            function rowValueForSort(row, key) {
                if (key === 'SecondsSinceLastUpdate') {
                    return currentSecondsSince(row);
                }

                if (key === 'State') {
                    return numericValue(row.State);
                }

                if (['RelandingCount', 'UnfocusCount', 'NaughtyCount', 'ExclusionCount'].includes(key)) {
                    return numericValue(row[key]);
                }

                return row[key] ?? '';
            }

            function compareRows(left, right) {
                const a = rowValueForSort(left, state.sortKey);
                const b = rowValueForSort(right, state.sortKey);

                if (a === b) {
                    return String(left.SubjectId).localeCompare(String(right.SubjectId), undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                if (a === null || a === '') {
                    return 1;
                }
                if (b === null || b === '') {
                    return -1;
                }

                let result = 0;
                if (typeof a === 'number' && typeof b === 'number') {
                    result = a - b;
                } else {
                    result = String(a).localeCompare(String(b), undefined, {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }

                return state.sortDir === 'asc' ? result : -result;
            }

            function buildSummaryCards(summary) {
                const cards = [
                    {
                        label: 'Participants',
                        value: summary.total,
                        note: 'Rows in the Register table'
                    },
                    {
                        label: 'In progress',
                        value: summary.active,
                        note: 'Not completed and not excluded'
                    },
                    {
                        label: 'Completed',
                        value: summary.completed,
                        note: 'Participants at state 6'
                    },
                    {
                        label: 'Excluded',
                        value: summary.excluded,
                        note: 'Participants at states -1 or -2'
                    },
                    {
                        label: 'Idle > 24h',
                        value: summary.stale24h,
                        note: 'Last server update older than one day'
                    }
                ];

                els.summaryGrid.innerHTML = cards.map(card => `
                    <article class="summary-card">
                        <p class="summary-label">${escapeHtml(card.label)}</p>
                        <p class="summary-value">${escapeHtml(card.value)}</p>
                        <p class="summary-note">${escapeHtml(card.note)}</p>
                    </article>
                `).join('');
            }

            function renderStateBreakdown(summary) {
                const chips = (summary.stateBreakdown || []).map(item => `
                    <span class="chip">
                        <strong>${escapeHtml(item.state === null ? '?' : item.state)}</strong>
                        <span>${escapeHtml(item.label)} (${escapeHtml(item.count)})</span>
                    </span>
                `);

                els.stateBreakdown.innerHTML = chips.join('');
            }

            function populateStateFilter(participants) {
                const current = els.stateFilter.value || 'all';
                const seen = new Map();

                participants.forEach(participant => {
                    const key = participant.State === null ? 'unknown' : String(participant.State);
                    if (!seen.has(key)) {
                        const label = participant.State === null
                            ? 'Unknown state'
                            : `State ${participant.State}: ${participant.StateLabel}`;
                        seen.set(key, label);
                    }
                });

                const options = ['<option value="all">All states</option>']
                    .concat(Array.from(seen.entries())
                        .sort((a, b) => a[0].localeCompare(b[0], undefined, { numeric: true }))
                        .map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`));

                els.stateFilter.innerHTML = options.join('');
                els.stateFilter.value = Array.from(seen.keys()).includes(current) || current === 'all'
                    ? current
                    : 'all';
            }

            function filteredParticipants() {
                const search = els.searchInput.value.trim().toLowerCase();
                const stateFilter = els.stateFilter.value;
                const idleFilter = els.idleFilter.value;

                return state.participants.filter(participant => {
                    if (stateFilter !== 'all') {
                        const participantState = participant.State === null ? 'unknown' : String(participant.State);
                        if (participantState !== stateFilter) {
                            return false;
                        }
                    }

                    if (idleFilter !== 'all') {
                        const secondsSince = currentSecondsSince(participant);
                        if (secondsSince === null || secondsSince < Number(idleFilter)) {
                            return false;
                        }
                    }

                    if (search === '') {
                        return true;
                    }

                    const haystack = [
                        participant.SubjectId,
                        participant.PoolId,
                        participant.State,
                        participant.StateLabel,
                        participant.GroupId,
                        participant.ClientTimeZone,
                        participant.BMY,
                        participant.Gender,
                        participant.Handedness,
                        participant.L1,
                        participant.LastServerUpdateSource,
                        participant.ImgPerm,
                        participant.FeedbackPreview
                    ].join(' ').toLowerCase();

                    return haystack.includes(search);
                });
            }

            function renderTable() {
                const rows = filteredParticipants().sort(compareRows);
                const searchText = els.searchInput.value.trim();
                const stateText = els.stateFilter.options[els.stateFilter.selectedIndex]?.text || 'All states';
                const idleText = els.idleFilter.options[els.idleFilter.selectedIndex]?.text || 'Any activity age';

                els.rowCountLabel.textContent = `${rows.length} participant${rows.length === 1 ? '' : 's'} shown`;
                els.filterSummaryLabel.textContent = `Filter: ${stateText}; idle: ${idleText}; search: ${searchText || 'none'}`;

                if (rows.length === 0) {
                    els.participantsBody.innerHTML = '<tr><td class="empty-state" colspan="25">No participants match the current filters.</td></tr>';
                    updateSortButtons();
                    return;
                }

                els.participantsBody.innerHTML = rows.map(row => {
                    const secondsSince = currentSecondsSince(row);
                    const feedbackCell = row.HasFeedback === 'Yes'
                        ? `<div title="${escapeHtml(row.FeedbackPreview || '')}"><strong>Yes</strong><br><span class="muted mono">${escapeHtml(row.FeedbackTimestamp || '-')}</span></div>`
                        : '<span class="muted">No</span>';
                    const imgPermShort = truncateText(row.ImgPerm || '', 28);
                    const poolIdShort = truncateText(row.PoolId || '', 24);
                    const targetLink = row.TargetUrl
                        ? `<a class="participant-link" href="${escapeHtml(row.TargetUrl)}" target="_blank" rel="noopener">Open current page</a>`
                        : '';

                    return `
                        <tr>
                            <td class="participant-cell">
                                <span class="participant-id mono">${escapeHtml(row.SubjectId || '-')}</span>
                                ${targetLink}
                            </td>
                            <td class="mono" title="${escapeHtml(row.PoolId || '')}">${escapeHtml(poolIdShort || '-')}</td>
                            <td class="mono">${escapeHtml(row.State === null ? '-' : row.State)}</td>
                            <td><span class="state-pill" data-tone="${escapeHtml(row.StateClass || 'unknown')}">${escapeHtml(row.StateLabel || 'Unknown')}</span></td>
                            <td class="mono">${formatTimestamp(row.LastServerUpdate)}</td>
                            <td class="heat-cell mono" data-seconds-base="${row.SecondsSinceLastUpdate === null ? '' : escapeHtml(row.SecondsSinceLastUpdate)}" style="${getHeatStyle(secondsSince)}">${escapeHtml(formatDuration(secondsSince))}</td>
                            <td>${escapeHtml(row.LastServerUpdateSource || '-')}</td>
                            <td class="mono">${escapeHtml(row.RelandingCount ?? 0)}</td>
                            <td class="mono">${escapeHtml(row.UnfocusCount ?? 0)}</td>
                            <td class="mono">${escapeHtml(row.NaughtyCount ?? 0)}</td>
                            <td class="mono">${escapeHtml(row.ExclusionCount ?? 0)}</td>
                            <td>${feedbackCell}</td>
                            <td class="mono">${escapeHtml(row.GroupId || '-')}</td>
                            <td>${escapeHtml(row.ClientTimeZone || '-')}</td>
                            <td>${escapeHtml(row.BMY || '-')}</td>
                            <td>${escapeHtml(row.Gender || '-')}</td>
                            <td>${escapeHtml(row.Handedness || '-')}</td>
                            <td>${escapeHtml(row.L1 || '-')}</td>
                            <td class="mono">${formatTimestamp(row.DateTime_Landing)}</td>
                            <td class="mono">${formatTimestamp(row.DateTime_Consent)}</td>
                            <td class="mono">${formatTimestamp(row.DateTime_Register)}</td>
                            <td class="mono">${formatTimestamp(row.DateTime_TIinstr)}</td>
                            <td class="mono">${formatTimestamp(row.DateTime_TItrain)}</td>
                            <td class="mono">${formatTimestamp(row.DateTime_TIprobe)}</td>
                            <td class="mono" title="${escapeHtml(row.ImgPerm || '')}">${escapeHtml(imgPermShort || '-')}</td>
                        </tr>
                    `;
                }).join('');

                updateRelativeCells();
                updateSortButtons();
            }

            function updateRelativeCells() {
                const cells = els.participantsBody.querySelectorAll('[data-seconds-base]');
                cells.forEach(cell => {
                    const baseText = cell.getAttribute('data-seconds-base');
                    if (baseText === '') {
                        cell.textContent = '-';
                        cell.style.cssText = '';
                        return;
                    }

                    const base = Number(baseText);
                    const seconds = Number.isNaN(base)
                        ? null
                        : base + Math.max(0, Math.floor((Date.now() - state.fetchedAtMs) / 1000));

                    cell.textContent = formatDuration(seconds);
                    cell.style.cssText = getHeatStyle(seconds);
                });
            }

            function updateSortButtons() {
                els.sortButtons.forEach(button => {
                    const active = button.dataset.sortKey === state.sortKey;
                    const baseLabel = button.dataset.label || button.textContent.trim();
                    button.dataset.label = baseLabel;
                    button.dataset.active = active ? 'true' : 'false';
                    const marker = active ? (state.sortDir === 'asc' ? ' ^' : ' v') : '';
                    button.textContent = baseLabel + marker;
                });
            }

            async function fetchDashboardData() {
                els.statusBanner.dataset.tone = 'ok';
                els.statusBanner.textContent = 'Refreshing dashboard data...';

                try {
                    const response = await fetch('./DashboardData.php', {
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });

                    if (response.status === 401) {
                        window.location.reload();
                        return;
                    }

                    const payload = await response.json();
                    if (!response.ok) {
                        throw new Error(payload.Notice || 'Dashboard request failed.');
                    }

                    state.participants = Array.isArray(payload.participants) ? payload.participants : [];
                    state.summary = payload.summary || {
                        total: 0,
                        active: 0,
                        completed: 0,
                        excluded: 0,
                        stale24h: 0,
                        stateBreakdown: []
                    };
                    state.fetchedAtMs = Date.now();

                    els.generatedAtLabel.textContent = `${payload.generatedAt} (${payload.serverTimeZone})`;
                    buildSummaryCards(state.summary);
                    renderStateBreakdown(state.summary);
                    populateStateFilter(state.participants);
                    renderTable();
                    els.statusBanner.dataset.tone = 'ok';
                    els.statusBanner.textContent = `Last refresh completed at ${payload.generatedAt} ${payload.serverTimeZone}.`;
                } catch (error) {
                    els.statusBanner.dataset.tone = 'error';
                    els.statusBanner.textContent = error.message || 'Failed to refresh dashboard data.';
                }
            }

            function installEventHandlers() {
                els.searchInput.addEventListener('input', renderTable);
                els.stateFilter.addEventListener('change', renderTable);
                els.idleFilter.addEventListener('change', renderTable);
                els.refreshButton.addEventListener('click', fetchDashboardData);

                els.sortButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const { sortKey } = button.dataset;
                        if (state.sortKey === sortKey) {
                            state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                        } else {
                            state.sortKey = sortKey;
                            state.sortDir = sortKey === 'State' ? 'asc' : 'desc';
                        }

                        renderTable();
                    });
                });

                window.setInterval(() => {
                    updateRelativeCells();
                }, 30000);

                window.setInterval(() => {
                    if (els.autoRefreshToggle.checked) {
                        fetchDashboardData();
                    }
                }, state.autoRefreshMs);
            }

            installEventHandlers();
            fetchDashboardData();
        })();
    </script>
<?php endif; ?>
</body>
</html>
