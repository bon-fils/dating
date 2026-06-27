<!-- Main Application Footer Section Container -->
<footer class="bg-white border-t border-slate-100 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Top Footer Branding and Navigation Links Split Grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pb-8 border-b border-slate-100">
            
            <!-- Brand Column Profile Descriptor -->
            <div class="md:col-span-5 space-y-4 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-tr from-pink-500 to-rose-500 rounded-lg flex items-center justify-center shadow-md shadow-pink-500/20">
                        <i class="fa-solid fa-heart text-white text-xs" aria-hidden="true"></i>
                    </div>
                    <span class="text-xl font-black tracking-wider uppercase text-slate-800">MatchMe</span>
                </div>
                <p class="text-slate-500 text-sm max-w-sm leading-relaxed">
                    Connecting verified people through aligned lifestyle metrics, smart algorithms, and personal style compatibility profiles.
                </p>
            </div>

            <!-- Contextual Section Routing Layout Links -->
            <div class="md:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center sm:text-left pt-4 md:pt-0">
                
                <!-- Features Link Sub-Group -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Explore</h4>
                    <ul class="space-y-2.5">
                        <li><a href="<?= isset($is_subpage) ? 'index.php#features' : '#features'; ?>" class="text-sm font-medium text-slate-600 hover:text-pink-500 transition">Features</a></li>
                    </ul>
                </div>

                <!-- About Link Sub-Group -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Company</h4>
                    <ul class="space-y-2.5">
                        <li><a href="<?= isset($is_subpage) ? 'index.php#about' : '#about'; ?>" class="text-sm font-medium text-slate-600 hover:text-pink-500 transition">About Us</a></li>
                    </ul>
                </div>

                <!-- Contact Link Sub-Group -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Support</h4>
                    <ul class="space-y-2.5">
                        <li><a href="<?= isset($is_subpage) ? 'index.php#contact' : '#contact'; ?>" class="text-sm font-medium text-slate-600 hover:text-pink-500 transition">Contact Us</a></li>
                    </ul>
                </div>

                <!-- FAQ Link Sub-Group -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Help Center</h4>
                    <ul class="space-y-2.5">
                        <li><a href="<?= isset($is_subpage) ? 'index.php#faq' : '#faq'; ?>" class="text-sm font-medium text-slate-600 hover:text-pink-500 transition">FAQ</a></li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Bottom Base Identity & Social Index Footer Strip -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs font-medium text-slate-400 text-center sm:text-left">
                &copy; <?= date('Y'); ?> MatchMe Engine Portal. Aligned for modern  connectivity. All rights reserved.
            </p>
            
            <!-- Quick Link Social FontAwesome Matrix Interface -->
            <div class="flex items-center space-x-4">
                <a href="#" class="w-8 h-8 rounded-full bg-slate-50 hover:bg-pink-50 text-slate-400 hover:text-pink-500 flex items-center justify-center transition" aria-label="Instagram">
                    <i class="fa-brands fa-instagram text-sm" aria-hidden="true"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-slate-50 hover:bg-pink-50 text-slate-400 hover:text-pink-500 flex items-center justify-center transition" aria-label="TikTok">
                    <i class="fa-brands fa-tiktok text-sm" aria-hidden="true"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-slate-50 hover:bg-pink-50 text-slate-400 hover:text-pink-500 flex items-center justify-center transition" aria-label="Twitter">
                    <i class="fa-brands fa-twitter text-sm" aria-hidden="true"></i>
                </a>
            </div>
        </div>

    </div>
</footer>

</main> <!-- Clean alignment close tag to terminate the structural header main tag container -->
</body>
</html>