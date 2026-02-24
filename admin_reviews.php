<?php
/**
 * GB Laser Soldering — Admin: Review Approval Panel
 * 
 * IMPORTANT: Add a password check here to protect this page.
 * This page lets you approve or delete customer reviews.
 * 
 * Access: yoursite.com/admin_reviews.php?key=YOUR_SECRET_KEY
 */

// ---- Simple secret key protection ----
define('ADMIN_KEY', 'gb2024admin');   // CHANGE THIS to a strong secret key!
$key = $_GET['key'] ?? $_POST['key'] ?? '';
if ($key !== ADMIN_KEY) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>403</title></head><body style="font-family:sans-serif;text-align:center;padding:80px;background:#0B0B0F;color:#9CA3AF"><h1 style="color:#D4AF37">403</h1><p>Access Denied. Provide the correct admin key.</p></body></html>');
}

require_once 'db_config.php';
$pdo = getDB();

// ---- Handle actions ----
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($_POST['action'] === 'approve') {
            $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?")->execute([$id]);
            $msg = "✅ Review #$id approved.";
        } elseif ($_POST['action'] === 'reject') {
            $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?")->execute([$id]);
            $msg = "🔄 Review #$id moved back to pending.";
        } elseif ($_POST['action'] === 'delete') {
            $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
            $msg = "🗑️ Review #$id deleted.";
        }
    }
}

// ---- Fetch all reviews ----
$tab    = $_GET['tab'] ?? 'pending';
$where  = $tab === 'approved' ? 'is_approved = 1' : 'is_approved = 0';
$all    = $pdo->query("SELECT * FROM reviews WHERE $where ORDER BY created_at DESC")->fetchAll();
$counts = $pdo->query("SELECT is_approved, COUNT(*) as cnt FROM reviews GROUP BY is_approved")->fetchAll(PDO::FETCH_KEY_PAIR);
$pendingCount  = (int)($counts[0] ?? 0);
$approvedCount = (int)($counts[1] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Review Approvals | GB Laser Soldering</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{gold:{DEFAULT:'#D4AF37',light:'#F5D77A',dark:'#B8860B'},surface:'#141419',bg:'#0B0B0F'}, fontFamily:{heading:['"Playfair Display"','serif'],body:['Montserrat','sans-serif']}}}}</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-bg text-[#F5F5F5] min-h-screen">

<div class="max-w-5xl mx-auto px-6 py-12">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-heading font-bold text-3xl gold-text">Review Approvals</h1>
            <p class="text-[#9CA3AF] text-sm mt-1">GB Laser Soldering — Admin Panel</p>
        </div>
        <a href="index.html" class="btn-outline text-sm">
            <i class="bi bi-arrow-left"></i> Back to Site
        </a>
    </div>

    <!-- Alert -->
    <?php if ($msg): ?>
    <div class="mb-6 p-4 glass-card text-sm text-[#F5D77A] border border-gold/30"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="glass-card p-5 text-center">
            <p class="text-3xl font-heading font-black gold-text"><?= $pendingCount ?></p>
            <p class="text-[#9CA3AF] text-sm mt-1"><i class="bi bi-hourglass-split me-1 text-gold"></i> Pending Approval</p>
        </div>
        <div class="glass-card p-5 text-center">
            <p class="text-3xl font-heading font-black gold-text"><?= $approvedCount ?></p>
            <p class="text-[#9CA3AF] text-sm mt-1"><i class="bi bi-check-circle me-1 text-gold"></i> Live / Approved</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-3 mb-7">
        <a href="?key=<?= htmlspecialchars(ADMIN_KEY) ?>&tab=pending"
           class="px-5 py-2 rounded-full text-sm border transition-all <?= $tab === 'pending' ? 'bg-gold text-black border-gold font-bold' : 'border-gold/20 text-[#9CA3AF] hover:border-gold hover:text-gold' ?>">
            Pending (<?= $pendingCount ?>)
        </a>
        <a href="?key=<?= htmlspecialchars(ADMIN_KEY) ?>&tab=approved"
           class="px-5 py-2 rounded-full text-sm border transition-all <?= $tab === 'approved' ? 'bg-gold text-black border-gold font-bold' : 'border-gold/20 text-[#9CA3AF] hover:border-gold hover:text-gold' ?>">
            Approved (<?= $approvedCount ?>)
        </a>
    </div>

    <!-- Review List -->
    <?php if (empty($all)): ?>
    <div class="text-center py-20 text-[#9CA3AF]">
        <i class="bi bi-inbox text-5xl text-gold/20 block mb-4"></i>
        <p>No <?= $tab ?> reviews.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($all as $r): ?>
        <div class="glass-card p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <!-- Info -->
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <span class="font-semibold text-[#F5D77A]"><?= htmlspecialchars($r['name']) ?></span>
                        <span class="text-xs bg-gold/10 text-gold border border-gold/20 rounded-full px-2 py-0.5"><?= htmlspecialchars($r['service']) ?></span>
                        <span class="flex gap-0.5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?> text-gold text-xs"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="text-[#9CA3AF] text-xs"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></span>
                    </div>
                    <p class="text-[#9CA3AF] text-sm leading-relaxed">"<?= htmlspecialchars($r['review_text']) ?>"</p>
                    <p class="text-[#9CA3AF]/40 text-[10px] mt-2">IP: <?= htmlspecialchars($r['ip_address'] ?? '—') ?> · ID #<?= $r['id'] ?></p>
                </div>
                <!-- Actions -->
                <div class="flex gap-2 flex-shrink-0">
                    <?php if ($r['is_approved'] == 0): ?>
                    <form method="POST" action="?key=<?= htmlspecialchars(ADMIN_KEY) ?>&tab=<?= $tab ?>">
                        <input type="hidden" name="key"    value="<?= htmlspecialchars(ADMIN_KEY) ?>">
                        <input type="hidden" name="id"     value="<?= $r['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button class="btn-primary !py-1.5 !px-4 !text-xs">
                            <i class="bi bi-check-lg"></i> Approve
                        </button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="?key=<?= htmlspecialchars(ADMIN_KEY) ?>&tab=<?= $tab ?>">
                        <input type="hidden" name="key"    value="<?= htmlspecialchars(ADMIN_KEY) ?>">
                        <input type="hidden" name="id"     value="<?= $r['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button class="btn-outline !py-1.5 !px-4 !text-xs">
                            <i class="bi bi-arrow-counterclockwise"></i> Unpublish
                        </button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="?key=<?= htmlspecialchars(ADMIN_KEY) ?>&tab=<?= $tab ?>"
                          onsubmit="return confirm('Delete this review permanently?')">
                        <input type="hidden" name="key"    value="<?= htmlspecialchars(ADMIN_KEY) ?>">
                        <input type="hidden" name="id"     value="<?= $r['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn-outline !py-1.5 !px-4 !text-xs border-red-500/40 text-red-400 hover:border-red-400 hover:bg-red-500/10">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
