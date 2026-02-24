<?php
/**
 * GB Laser Soldering — All Reviews Page
 * Displays all approved reviews with star filter, avg rating stats,
 * pagination, and the submit-a-review form.
 */
require_once 'db_config.php';

// Pagination & filter
$page       = max(1, (int)($_GET['page']   ?? 1));
$filter     = (int)($_GET['rating'] ?? 0);
$perPage    = 9;
$offset     = ($page - 1) * $perPage;

$pdo        = getDB();
$ratingSQL  = ($filter >= 1 && $filter <= 5) ? 'AND rating = :rating' : '';

// Totals
$cStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE is_approved = 1 $ratingSQL");
if ($filter) $cStmt->bindValue(':rating', $filter, PDO::PARAM_INT);
$cStmt->execute();
$total     = (int)$cStmt->fetchColumn();
$totalPages= (int)ceil($total / $perPage);

// Stats
$sRow   = $pdo->query("SELECT ROUND(AVG(rating),1) as avg, COUNT(*) as cnt FROM reviews WHERE is_approved = 1")->fetch();
$avgRating    = round((float)($sRow['avg'] ?? 0), 1);
$totalReviews = (int)($sRow['cnt'] ?? 0);

// Distribution
$distRows = $pdo->query("SELECT rating, COUNT(*) as cnt FROM reviews WHERE is_approved = 1 GROUP BY rating ORDER BY rating DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$dist = [];
for ($i = 5; $i >= 1; $i--) {
    $count   = (int)($distRows[$i] ?? 0);
    $dist[$i]= ['count' => $count, 'pct' => $totalReviews > 0 ? round($count / $totalReviews * 100) : 0];
}

// Fetch page
$stmt = $pdo->prepare("SELECT id, name, service, rating, review_text, created_at FROM reviews WHERE is_approved = 1 $ratingSQL ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
if ($filter) $stmt->bindValue(':rating', $filter, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews — GB Laser Soldering</title>
    <meta name="description" content="Read genuine customer reviews of GB Laser Soldering — Vijayawada's trusted laser jewelry repair and NG gold testing experts.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{gold:{DEFAULT:'#D4AF37',light:'#F5D77A',dark:'#B8860B'},surface:'#141419',bg:'#0B0B0F'},fontFamily:{heading:['"Playfair Display"','serif'],body:['Montserrat','sans-serif']}}}}</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-bg text-[#F5F5F5]">

<!-- NAVBAR -->
<header class="fixed top-0 left-0 w-full z-[999]">
    <div class="flex items-center justify-between px-6 md:px-12 py-4 bg-black/90 backdrop-blur-xl border-b border-gold/20">
        <a href="index.html" class="flex items-center gap-3 group">
            <img src="images/gb_logo.jpeg" alt="GB Logo" class="h-10 w-auto rounded-lg object-contain group-hover:scale-105 transition-transform duration-300">
            <span class="font-heading font-bold text-lg tracking-widest gold-text">GB LASER SOLDERING</span>
        </a>
        <nav class="hidden md:flex items-center gap-8">
            <a href="index.html"    class="nav-link">Home</a>
            <a href="about.html"    class="nav-link">About</a>
            <a href="services.html" class="nav-link">Services</a>
            <a href="gallery.html"  class="nav-link">Gallery</a>
            <a href="contact.html"  class="nav-link">Contact</a>
        </nav>
    </div>
</header>

<!-- HERO -->
<section class="pt-40 pb-12 px-6 text-center">
    <div class="max-w-4xl mx-auto">
        <span class="section-label"><i class="bi bi-star-fill me-1"></i> Genuine Feedback</span>
        <h1 class="font-heading font-black text-4xl md:text-5xl gold-text mt-2">Customer Reviews</h1>
        <p class="text-[#9CA3AF] mt-3 text-sm">Trusted by jewellers and individuals across Vijayawada</p>
        <span class="section-title-line"></span>
    </div>
</section>

<!-- STATS STRIP + RATING DISTRIBUTION -->
<section class="pb-12 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="glass-card p-8 md:p-10 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

            <!-- Average score + big number -->
            <div class="text-center md:border-r md:border-gold/10 pr-0 md:pr-10">
                <p class="text-7xl font-heading font-black gold-text"><?= number_format($avgRating, 1) ?></p>
                <div class="flex justify-center gap-1 my-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi <?= $i <= round($avgRating) ? 'bi-star-fill' : ($i - 0.5 <= $avgRating ? 'bi-star-half' : 'bi-star') ?> text-gold text-xl"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-[#9CA3AF] text-sm">Based on <strong class="text-[#F5F5F5]"><?= $totalReviews ?></strong> verified reviews</p>
            </div>

            <!-- Distribution bars -->
            <div class="space-y-2">
                <?php foreach ($dist as $stars => $d): ?>
                <a href="?rating=<?= $stars ?>" class="flex items-center gap-3 group">
                    <span class="text-xs text-[#9CA3AF] w-4"><?= $stars ?></span>
                    <i class="bi bi-star-fill text-gold text-xs flex-shrink-0"></i>
                    <div class="flex-grow h-2 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-gold-dark to-gold rounded-full group-hover:brightness-110 transition-all"
                             style="width: <?= $d['pct'] ?>%"></div>
                    </div>
                    <span class="text-xs text-[#9CA3AF] w-8 text-right"><?= $d['count'] ?></span>
                </a>
                <?php endforeach; ?>
                <?php if ($filter): ?>
                    <a href="reviews.php" class="text-xs text-gold hover:text-gold-light transition-colors mt-2 inline-block">✕ Clear filter</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- REVIEW CARDS GRID -->
<section class="pb-16 px-6">
    <div class="max-w-6xl mx-auto">

        <?php if (empty($reviews)): ?>
        <div class="text-center py-20 text-[#9CA3AF]">
            <i class="bi bi-chat-quote text-5xl text-gold/30 block mb-4"></i>
            <p>No reviews found<?= $filter ? " for {$filter}-star rating" : '' ?>.</p>
            <?php if ($filter): ?>
                <a href="reviews.php" class="text-gold hover:text-gold-light text-sm mt-2 inline-block">View all reviews →</a>
            <?php endif; ?>
        </div>
        <?php else: ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php foreach ($reviews as $r): ?>
            <div class="glass-card p-6 flex flex-col relative h-full">
                <span class="absolute top-4 right-5 text-gold/15 font-heading font-black text-6xl leading-none select-none">"</span>

                <!-- Stars -->
                <div class="flex gap-0.5 mb-3 relative z-10">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?> text-gold text-sm"></i>
                    <?php endfor; ?>
                </div>

                <!-- Review -->
                <p class="text-[#9CA3AF] text-sm leading-relaxed flex-grow mb-5 relative z-10">
                    "<?= htmlspecialchars($r['review_text']) ?>"
                </p>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4 border-t border-gold/10 relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gold/20 flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-person-fill text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-[#F5D77A] text-sm"><?= htmlspecialchars($r['name']) ?></p>
                            <p class="text-[#9CA3AF] text-xs"><?= htmlspecialchars($r['service']) ?></p>
                        </div>
                    </div>
                    <span class="text-[#9CA3AF] text-[10px]"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&rating=<?= $filter ?>" class="btn-outline !py-2 !px-4 text-sm">
                    <i class="bi bi-chevron-left"></i>
                </a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?page=<?= $p ?>&rating=<?= $filter ?>"
                   class="w-9 h-9 flex items-center justify-center rounded-lg text-sm border transition-all
                          <?= $p === $page ? 'bg-gold text-black border-gold font-bold' : 'border-gold/20 text-[#9CA3AF] hover:border-gold hover:text-gold' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>&rating=<?= $filter ?>" class="btn-outline !py-2 !px-4 text-sm">
                    <i class="bi bi-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<!-- SUBMIT A REVIEW -->
<section class="section-surface py-20 px-6" id="write-review">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-10">
            <span class="section-label"><i class="bi bi-pencil-square me-1"></i> Share Your Experience</span>
            <h2 class="font-heading font-bold text-3xl gold-text mt-2">Write a Review</h2>
            <p class="text-[#9CA3AF] text-sm mt-2">Your review will appear after a quick approval.</p>
            <span class="section-title-line"></span>
        </div>

        <!-- Alert box -->
        <div id="review-alert" class="hidden mb-6 p-4 rounded-xl text-sm font-medium"></div>

        <form id="review-form" class="glass-card p-8 space-y-5">
            <!-- Name -->
            <div>
                <label class="block text-sm text-[#F5D77A] font-semibold mb-2">Your Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" maxlength="100" required placeholder="e.g. Ramesh K."
                       class="w-full bg-white/5 border border-gold/20 rounded-xl px-4 py-3 text-sm text-[#F5F5F5] placeholder-[#9CA3AF]/60 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all">
            </div>

            <!-- Service -->
            <div>
                <label class="block text-sm text-[#F5D77A] font-semibold mb-2">Service Availed <span class="text-red-400">*</span></label>
                <select name="service" class="w-full bg-[#141419] border border-gold/20 rounded-xl px-4 py-3 text-sm text-[#F5F5F5] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all">
                    <option value="General">General</option>
                    <option value="Laser Gold Soldering">Laser Gold Soldering</option>
                    <option value="Laser Silver Soldering">Laser Silver Soldering</option>
                    <option value="Precision Stone Setting">Precision Stone Setting</option>
                    <option value="Laser Jewelry Repairs">Laser Jewelry Repairs</option>
                    <option value="NG Gold Testing">NG Gold Testing</option>
                </select>
            </div>

            <!-- Star Rating Picker -->
            <div>
                <label class="block text-sm text-[#F5D77A] font-semibold mb-2">Your Rating <span class="text-red-400">*</span></label>
                <div class="flex gap-2" id="star-picker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" data-val="<?= $i ?>"
                            class="star-btn text-3xl text-[#9CA3AF]/30 hover:text-gold transition-all duration-150 leading-none">
                        ★
                    </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating-val" value="5">
            </div>

            <!-- Review Text -->
            <div>
                <label class="block text-sm text-[#F5D77A] font-semibold mb-2">Your Review <span class="text-red-400">*</span></label>
                <textarea name="review_text" maxlength="1000" rows="5" required
                          placeholder="Tell others about your experience (min. 20 characters)..."
                          class="w-full bg-white/5 border border-gold/20 rounded-xl px-4 py-3 text-sm text-[#F5F5F5] placeholder-[#9CA3AF]/60 focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold transition-all resize-none"></textarea>
                <p class="text-[#9CA3AF] text-xs mt-1"><span id="char-count">0</span> / 1000 characters</p>
            </div>

            <button type="submit" class="btn-primary w-full justify-center" id="submit-btn">
                <i class="bi bi-send"></i> Submit Review
            </button>
        </form>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-surface border-t border-gold/10 py-12 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="footer-divider mb-8"></div>
        <div class="text-center text-[#9CA3AF] text-xs space-y-2">
            <div class="flex flex-wrap justify-center gap-x-6 gap-y-1 mb-3">
                <a href="privacy-policy.html" class="hover:text-gold transition-colors">Privacy Policy</a>
                <a href="disclaimer.html"      class="hover:text-gold transition-colors">Disclaimer</a>
                <a href="terms.html"           class="hover:text-gold transition-colors">Terms &amp; Conditions</a>
                <a href="sitemap.html"         class="hover:text-gold transition-colors">Sitemap</a>
            </div>
            <p>© 2024 GB LASER SOLDERING. All rights reserved.</p>
            <p>Powered by <a href="https://davishkar.github.io/My-Services-Page/" target="_blank" rel="noopener noreferrer" class="text-gold font-semibold">Avishkar Digital Studio</a></p>
        </div>
    </div>
</footer>

<script>
// ---- Star picker ----
const stars   = document.querySelectorAll('.star-btn');
const ratingInput = document.getElementById('rating-val');
let selectedRating = 5;

function paintStars(n) {
    stars.forEach((s, i) => {
        s.classList.toggle('text-gold', i < n);
        s.classList.toggle('text-[#9CA3AF]/30', i >= n);
    });
}
paintStars(5);

stars.forEach(btn => {
    btn.addEventListener('mouseover', () => paintStars(+btn.dataset.val));
    btn.addEventListener('mouseleave', () => paintStars(selectedRating));
    btn.addEventListener('click', () => {
        selectedRating = +btn.dataset.val;
        ratingInput.value = selectedRating;
        paintStars(selectedRating);
    });
});

// ---- Char counter ----
const textarea = document.querySelector('textarea[name="review_text"]');
const charCount = document.getElementById('char-count');
textarea.addEventListener('input', () => charCount.textContent = textarea.value.length);

// ---- Form submit ----
const form      = document.getElementById('review-form');
const alertBox  = document.getElementById('review-alert');
const submitBtn = document.getElementById('submit-btn');

form.addEventListener('submit', async e => {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
    alertBox.className = 'hidden';

    const data = new FormData(form);
    try {
        const res  = await fetch('submit_review.php', { method: 'POST', body: data });
        const json = await res.json();
        alertBox.className = `mb-6 p-4 rounded-xl text-sm font-medium ${json.success ? 'bg-green-500/10 border border-green-500/30 text-green-400' : 'bg-red-500/10 border border-red-500/30 text-red-400'}`;
        alertBox.textContent = json.message;
        if (json.success) { form.reset(); selectedRating = 5; paintStars(5); charCount.textContent = '0'; }
    } catch {
        alertBox.className = 'mb-6 p-4 rounded-xl text-sm font-medium bg-red-500/10 border border-red-500/30 text-red-400';
        alertBox.textContent = 'Network error. Please try again.';
    }
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-send"></i> Submit Review';
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});
</script>
</body>
</html>
