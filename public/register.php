<?php
$is_subpage = true;
include __DIR__ . '/../includes/header.php';
?>

<!-- MatchMe Exact Gradient Background Profile from Screenshot 2026-06-01 111151.png -->
<div class="relative flex-grow flex items-center justify-center p-4 sm:p-6 md:p-8 bg-gradient-to-br from-[#4c1d5c] via-[#cb356b] to-[#bd5f1b] min-h-screen font-sans">
    
    <!-- Precise 32px rounded high-contrast card -->
    <div class="relative bg-white px-8 py-10 rounded-[32px] shadow-2xl w-full max-w-4xl my-auto border border-white/10">
        
        <!-- Header: Centered Bold Branding -->
        <div class="text-center mb-6">
            <h1 class="text-4xl font-black tracking-wide text-[#e93266]">
                MATCHME
            </h1>
            <p class="text-[#718096] text-xs mt-1 font-semibold">Create your account to start matching</p>
        </div>

        <!-- Alert Notification System -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
                <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span class="w-full"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm">
                <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span class="w-full"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Framework -->
        <form action="../actions/register_action.php" method="POST" class="space-y-5">
            
            <!-- SECTION 1: ACCOUNT SECURITY -->
            <div class="space-y-3">
                <div class="text-[11px] font-bold text-[#a0aec0] uppercase tracking-wider">Account Security</div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="e.g., johndoe">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="you@example.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Password</label>
                        <input type="password" name="password" required minlength="6" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="Min. 6 characters">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="Re-type password">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ABOUT YOU -->
            <div class="space-y-3 pt-1">
                <div class="text-[11px] font-bold text-[#a0aec0] uppercase tracking-wider">About You</div>
                
                <!-- Row 1: Location, Gender, Age, Height -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-5">
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Location (City)</label>
                        <input type="text" name="location" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="e.g., Kigali">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Gender</label>
                        <div class="relative">
                            <select name="gender" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-700 cursor-pointer appearance-none transition-all">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Age</label>
                        <input type="number" name="age" min="18" max="100" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="18">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Height (cm)</label>
                        <input type="number" step="0.1" name="height" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="175">
                    </div>
                </div>

                <!-- Row 2: Weight, Education, Outfit Style, Meal Type -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-800 placeholder-slate-400/80 transition-all" placeholder="70">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Education</label>
                        <div class="relative">
                            <select name="education_level" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-700 cursor-pointer appearance-none transition-all">
                                <option value="high_school">High School</option>
                                <option value="diploma">Diploma</option>
                                <option value="bachelor">Bachelor</option>
                                <option value="master">Master</option>
                                <option value="phd">PhD</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Outfit Style</label>
                        <div class="relative">
                            <select name="outfit_style" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-700 cursor-pointer appearance-none transition-all">
                                <option value="casual">Casual</option>
                                <option value="formal">Formal</option>
                                <option value="streetwear">Streetwear</option>
                                <option value="sporty">Sporty</option>
                                <option value="traditional">Traditional</option>
                                <option value="luxury">Luxury</option>
                                <option value="minimalist">Minimalist</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#2d3748] mb-1">Meal Type</label>
                        <div class="relative">
                            <select name="meal_type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-[#e93266] focus:ring-2 focus:ring-[#e93266]/20 text-sm bg-white text-slate-700 cursor-pointer appearance-none transition-all">
                                <option value="mixed">Mixed</option>
                                <option value="traditional">Traditional</option>
                                <option value="fast_food">Fast Food</option>
                                <option value="vegetarian">Vegetarian</option>
                                <option value="vegan">Vegan</option>
                                <option value="halal">Halal</option>
                                <option value="kosher">Kosher</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vibrant Action Button matching screenshot palette -->
            <button type="submit" class="w-full bg-[#e93266] hover:bg-[#d22655] text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-pink-500/20 active:scale-[0.99] transition-all mt-4 tracking-wide text-sm">
                Create Account
            </button>
        </form>

        <!-- Dynamic Card Footer Links -->
        <div class="text-center mt-6">
            <p class="text-[#718096] text-xs font-semibold">
                Already have an account? <a href="login.php" class="text-[#e93266] font-bold hover:underline ml-1">Log in here</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>