<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl text-sm font-medium flex items-start gap-2 shadow-sm">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <span><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-sm font-medium flex items-start gap-2 shadow-sm">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <span><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 1. HERO CONTAINER (Polished Text Layout & Integrated Couple BG Image) -->
<!-- ========================================================================= -->
<div class="relative min-h-[85vh] flex items-center overflow-hidden border-b border-slate-100/60 bg-slate-900">
    
    <!-- Background Image Layer with Gradient Masks for Maximum Readability -->
    <div class="absolute inset-0 w-full h-full bg-cover bg-center bg-no-repeat pointer-events-none z-0 scale-105 animate-fade-in" 
         style="background-image: url('https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&q=80&w=1920');">
    </div>
    
    <!-- Advanced Multi-Layer Gradient Overlays to smoothly isolates content and mask image -->
    <div class="absolute inset-0 bg-gradient-to-r from-white via-white/95 to-transparent z-1"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-slate-50/50 via-transparent to-white z-1"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 relative z-10 w-full">
        <div class="max-w-3xl space-y-6 text-left">
            <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-semibold bg-pink-50 text-pink-600 border border-pink-100/40 shadow-sm animate-pulse">
                <i class="fa-solid fa-sparkles"></i> The Smart Lifestyle Matchmaker
            </span>
            <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-slate-900 leading-tight sm:leading-none">
                Find Your Perfect Match <br class="hidden sm:inline">Based on <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-rose-500">Your Lifestyle</span>
            </h1>
            <p class="text-slate-600 text-lg sm:text-xl max-w-2xl leading-relaxed">
                Skip the shallow swiping. Connect with people based on real, everyday compatibility — from day-to-day routines and outfit aesthetics to shared culinary tastes.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-start gap-4 pt-4">
                <a href="register.php" class="w-full sm:w-auto bg-gradient-to-r from-pink-500 to-rose-500 text-white font-bold px-8 py-4 rounded-xl hover:opacity-95 shadow-lg shadow-pink-500/20 transition-all transform hover:-translate-y-0.5 text-center">
                    Get Started Free
                </a>
                <a href="#features" class="w-full sm:w-auto bg-white/80 backdrop-blur-sm hover:bg-slate-100 text-slate-700 font-semibold px-8 py-4 rounded-xl transition-all border border-slate-200/60 text-center shadow-sm">
                    See How It Works
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. FEATURES GRID (Clean Soft Off-White Layer with Transparent Cards) -->
<!-- ========================================================================= -->
<section id="features" class="py-20 sm:py-24 bg-slate-50/60 scroll-mt-16 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-pink-500">Engine Features</h2>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Built for Deep Compatibility</p>
            <p class="text-slate-500 text-sm sm:text-base">We look beyond just pictures to connect you on things that matter daily.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-2xl border border-slate-200/50 shadow-sm hover:shadow-md hover:border-pink-200/40 transition duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-pink-50 text-pink-500 flex items-center justify-center mb-6 text-xl shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Style Matching</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Whether you lean towards Streetwear, Minimalist, Vintage, or Casual Chic, find someone whose look complements your vibe.</p>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-2xl border border-slate-200/50 shadow-sm hover:shadow-md hover:border-pink-200/40 transition duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center mb-6 text-xl shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Dietary Harmony</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Love traditional cooking, vegan cuisine, or healthy organic meals? Match with people who want to share the same table options.</p>
            </div>
            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-2xl border border-slate-200/50 shadow-sm hover:shadow-md hover:border-pink-200/40 transition duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center mb-6 text-xl shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Professional Alignment</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Coordinate based on lifestyle focus areas, working vibes, and daily schedules so your social hours seamlessly blend together.</p>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 3. ABOUT US SECTION (Pure Snow White Isolation Layout) -->
<!-- ========================================================================= -->
<section id="about" class="py-20 sm:py-24 bg-white scroll-mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-pink-500">Our Story</h2>
                <h3 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Fixing Modern Matchmaking</h3>
                <p class="text-slate-600 leading-relaxed">
                    MatchMe was built to eliminate superficial tracking mechanics. We noticed that typical modern matching platforms focused entirely on shallow, short-term variables, leading to dead-end conversations and misaligned expectations. 
                </p>
                <p class="text-slate-600 leading-relaxed">
                    By analyzing core lifestyle elements, stylistic presentation aesthetics, and day-to-day routine metrics, our alignment engine builds a genuine compatibility ranking index that respects who you are.
                </p>
                <div class="pt-2">
                    <blockquote class="border-l-4 border-pink-500 pl-4 text-slate-500 italic text-sm">
                        "We believe real, sustainable connection is discovered through shared routines, mutual values, and complementary tastes."
                    </blockquote>
                </div>
            </div>
            <!-- Premium Radial Graphic Mock Canvas -->
            <div class="bg-gradient-to-b from-pink-50 to-rose-100/60 rounded-3xl p-8 relative h-72 sm:h-96 flex items-center justify-center overflow-hidden border border-pink-100/30 shadow-sm">
                <div class="text-center z-10 space-y-2">
                    <p class="text-5xl font-black text-slate-800 tracking-tight">100%</p>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Verified Profile Matrix</p>
                </div>
                <div class="absolute inset-0 bg-[radial-gradient(#ec489915_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
                <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/40 rounded-full blur-xl"></div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 4. FAQ SECTION (Linear Soft Transition Space) -->
<!-- ========================================================================= -->
<section id="faq" class="py-20 sm:py-24 bg-gradient-to-b from-slate-50/50 via-slate-50 to-slate-50/50 scroll-mt-16 border-t border-b border-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-pink-500">FAQ</h2>
            <p class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Frequently Asked Questions</p>
        </div>

        <div class="space-y-4">
            <!-- Question 1 -->
            <div class="faq-item bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition duration-200">
                <button type="button" class="faq-trigger w-full px-6 py-5 text-left flex items-center justify-between font-bold text-slate-800 focus:outline-none text-sm sm:text-base hover:text-pink-500 transition-colors" aria-expanded="false" aria-controls="faq-content-1">
                    <span>How does the compatibility ranking engine operate?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" aria-hidden="true"></i>
                </button>
                <div id="faq-content-1" class="faq-content max-h-0 opacity-0 overflow-hidden transition-all duration-200 ease-in-out bg-slate-50/30" aria-hidden="true">
                    <p class="px-6 pb-5 text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Our engine uses programmatic criteria weight metrics to evaluate shared daily routines, design preferences, and food alignments, mapping user profiles together effectively without relying on superficial data loops.
                    </p>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="faq-item bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition duration-200">
                <button type="button" class="faq-trigger w-full px-6 py-5 text-left flex items-center justify-between font-bold text-slate-800 focus:outline-none text-sm sm:text-base hover:text-pink-500 transition-colors" aria-expanded="false" aria-controls="faq-content-2">
                    <span>Is my personal account profile data secure?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" aria-hidden="true"></i>
                </button>
                <div id="faq-content-2" class="faq-content max-h-0 opacity-0 overflow-hidden transition-all duration-200 ease-in-out bg-slate-50/30" aria-hidden="true">
                    <p class="px-6 pb-5 text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Security remains a vital protocol condition. All access vectors, preferences, and personal information properties are safely managed within our isolated database layer, protecting you from public exposure.
                    </p>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="faq-item bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition duration-200">
                <button type="button" class="faq-trigger w-full px-6 py-5 text-left flex items-center justify-between font-bold text-slate-800 focus:outline-none text-sm sm:text-base hover:text-pink-500 transition-colors" aria-expanded="false" aria-controls="faq-content-3">
                    <span>Can I modify my lifestyle matching options later?</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" aria-hidden="true"></i>
                </button>
                <div id="faq-content-3" class="faq-content max-h-0 opacity-0 overflow-hidden transition-all duration-200 ease-in-out bg-slate-50/30" aria-hidden="true">
                    <p class="px-6 pb-5 text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Absolutely. You can update your aesthetic parameters, dietary options, or scheduling variables instantly inside your account profile workspace dashboard at any time.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 5. CONTACT US SECTION (Premium Split Card with Rich Gradient Frame) -->
<!-- ========================================================================= -->
<section id="contact" class="py-20 sm:py-28 bg-white scroll-mt-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-b from-white to-slate-50/60 rounded-3xl border border-slate-200/60 shadow-xl overflow-hidden p-8 sm:p-12 relative">
            
            <div class="text-center max-w-2xl mx-auto mb-10 space-y-2 relative z-10">
                <h2 class="text-xs font-bold uppercase tracking-wider text-pink-500">Get In Touch</h2>
                <h3 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">Have Questions or Feedback?</h3>
                <p class="text-slate-500 text-sm">Drop us a line and our system support operators will get right back to you.</p>
            </div>

            <form id="contactForm" class="space-y-5 relative z-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Full Name</label>
                        <input type="text" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm transition bg-white shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Email Address</label>
                        <input type="email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm transition bg-white shadow-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Message</label>
                    <textarea rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm transition bg-white shadow-sm placeholder-slate-400" placeholder="How can we help your match experience?"></textarea>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-semibold py-3 rounded-xl hover:bg-slate-800 shadow-md transition text-sm tracking-wide active:scale-[0.99]">
                    Send Message Securely
                </button>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();
            if (window.MatchMeToast) {
                MatchMeToast.show('Thanks for your message. Our team will get back to you soon.', 'success');
            }
            contactForm.reset();
        });
    }

    const faqItems = document.querySelectorAll(".faq-item");

    faqItems.forEach(item => {
        const trigger = item.querySelector(".faq-trigger");
        const content = item.querySelector(".faq-content");
        const icon = trigger.querySelector("i");

        trigger.addEventListener("click", () => {
            const isOpen = trigger.getAttribute("aria-expanded") === "true";

            faqItems.forEach(otherItem => {
                const otherTrigger = otherItem.querySelector(".faq-trigger");
                const otherContent = otherItem.querySelector(".faq-content");
                const otherIcon = otherItem.querySelector(".faq-trigger i");

                otherContent.style.maxHeight = "0px";
                otherContent.classList.add("opacity-0");
                otherContent.setAttribute("aria-hidden", "true");
                otherTrigger.setAttribute("aria-expanded", "false");
                otherIcon.classList.remove("rotate-180");
            });

            if (!isOpen) {
                content.classList.remove("opacity-0");
                content.style.maxHeight = content.scrollHeight + "px";
                content.setAttribute("aria-hidden", "false");
                trigger.setAttribute("aria-expanded", "true");
                icon.classList.add("rotate-180");
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>