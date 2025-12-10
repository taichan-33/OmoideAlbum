<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isScrolled = ref(false);
const scrollY = ref(0);

// Intersection Observer for scroll animations
const observer = ref(null);
const animatedElements = ref([]);

// 3D Tilt Ref and Logic
const tiltElements = ref([]);
const handleMouseMove = (e, el) => {
    const rect = el.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = ((y - centerY) / centerY) * -5; // Limit rotation deg
    const rotateY = ((x - centerX) / centerX) * 5;

    el.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
};
const handleMouseLeave = (el) => {
    el.style.transform = `perspective(1000px) rotateX(0) rotateY(0) scale(1)`;
};

const setRef = (el) => {
    if (el) animatedElements.value.push(el);
};

onMounted(() => {
    const handleScroll = () => {
        scrollY.value = window.scrollY;
        isScrolled.value = window.scrollY > 50;
    };
    window.addEventListener("scroll", handleScroll);

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.value.unobserve(entry.target); // Trigger only once
                }
            });
        },
        {
            threshold: 0.15,
            rootMargin: "0px 0px -50px 0px",
        }
    );

    animatedElements.value.forEach((el) => observer.value.observe(el));
});

onUnmounted(() => {
    if (observer.value) observer.value.disconnect();
    window.removeEventListener("scroll", () => {}); // Simplified cleanup
});
</script>

<template>
    <Head title="Welcome to Omoide Album" />

    <div
        class="min-h-screen bg-[#0B1120] text-white font-serif selection:bg-amber-500 selection:text-white overflow-x-hidden"
    >
        <!-- Navbar -->
        <nav
            class="fixed top-0 w-full z-50 transition-all duration-700 border-b border-transparent"
            :class="{
                'bg-[#0B1120]/90 backdrop-blur-xl border-white/5 py-3 shadow-2xl':
                    isScrolled,
                'py-6 bg-transparent': !isScrolled,
            }"
        >
            <div
                class="max-w-7xl mx-auto px-6 flex justify-between items-center"
            >
                <div class="flex items-center gap-3 group cursor-pointer">
                    <!-- Icon -->
                    <div
                        class="relative w-10 h-10 rounded-full flex items-center justify-center overflow-hidden transition-transform duration-500 group-hover:rotate-12"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-tr from-amber-500/20 to-transparent border border-amber-500/30 rounded-full"
                        ></div>
                        <i class="bi bi-journal-album text-amber-500 text-lg relative z-10"></i>
                    </div>
                    <span
                        class="text-xl font-bold tracking-[0.2em] text-amber-500/90 group-hover:text-amber-400 transition-colors duration-500"
                        >OMOiDE</span
                    >
                </div>

                <div class="flex items-center gap-6">
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="text-sm font-medium text-gray-400 hover:text-white transition-colors tracking-widest relative group"
                    >
                        LOGIN
                         <span class="absolute -bottom-1 left-0 w-0 h-[1px] bg-white transition-all duration-300 group-hover:w-full"></span>
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="relative px-8 py-2.5 overflow-hidden group rounded-sm"
                    >
                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-amber-700 to-amber-600 opacity-80 group-hover:opacity-100 transition-opacity duration-300"></span>
                        <span class="absolute bottom-0 right-0 block w-64 h-64 mb-32 mr-4 transition duration-500 origin-bottom-left transform rotate-45 translate-x-24 bg-white opacity-10 group-hover:rotate-90 ease"></span>
                        <span class="relative text-white text-xs font-bold tracking-[0.15em] uppercase">Start Free</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header
            class="relative h-screen flex items-center justify-center overflow-hidden perspective-1000"
        >
            <!-- Background Image with Ken Burns Effect -->
            <div
                class="absolute inset-0 z-0"
                style="will-change: transform;"
            >
                 <div class="absolute inset-0 bg-[#0B1120]" >
                     <img 
                        src="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=2070&auto=format&fit=crop" 
                        class="w-full h-full object-cover animate-ken-burns opacity-60"
                        alt="Hero Background"
                     />
                 </div>
                <div
                    class="absolute inset-0 bg-gradient-to-b from-[#0B1120]/30 via-[#0B1120]/50 to-[#0B1120] z-10"
                ></div>
                <!-- Grain Overlay -->
                <div class="absolute inset-0 opacity-[0.03] bg-[url('https://grainy-gradients.vercel.app/noise.svg')] z-10"></div>
            </div>

            <!-- Content -->
            <div
                class="relative z-20 text-center px-4 max-w-5xl mx-auto flex flex-col items-center"
                :style="{ transform: `translateY(${scrollY * 0.3}px)` }"
            >
                <div
                    class="inline-block mb-8 px-6 py-2 border border-amber-500/20 rounded-full bg-amber-900/10 backdrop-blur-md animate-fade-in-down"
                >
                    <span
                        class="text-[10px] md:text-xs tracking-[0.4em] text-amber-300 uppercase font-sans font-light"
                        >Timeless Memories</span
                    >
                </div>

                <h1
                    class="text-6xl md:text-8xl lg:text-9xl font-black mb-10 leading-[1.1] tracking-tighter animate-fade-in-up delay-200"
                >
                    <span class="block text-white">ふたりの物語を、</span>
                    <span
                        class="bg-clip-text text-transparent bg-gradient-to-r from-amber-200 via-yellow-100 to-amber-300 animate-shimmer"
                        style="background-size: 200% auto;"
                    >
                        美しく残そう。
                    </span>
                </h1>

                <p
                    class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto mb-16 leading-loose tracking-wide font-sans font-light animate-fade-in-up delay-400 opacity-0 fill-mode-forwards"
                >
                    何気ない日常も、特別な旅も。<br />
                    Omoide Albumは、ふたりの大切な時間を<br class="md:hidden"/>永遠に色褪せない記録に変えるデジタルアルバムです。
                </p>

                <div
                    class="flex flex-col sm:flex-row gap-8 animate-fade-in-up delay-500 opacity-0 fill-mode-forwards"
                >
                    <Link
                        :href="route('register')"
                        class="group relative px-10 py-5 bg-transparent overflow-hidden rounded-sm transition-all hover:scale-105 duration-500 border border-amber-500/30 hover:border-amber-400/60"
                    >
                        <div
                            class="absolute inset-0 w-0 bg-amber-600/20 transition-all duration-[400ms] ease-out group-hover:w-full"
                        ></div>
                        <span
                            class="relative text-amber-100 group-hover:text-white font-medium tracking-[0.2em] flex items-center gap-4 text-sm uppercase"
                        >
                            Start Your Journey
                            <i class="bi bi-arrow-right transition-transform group-hover:translate-x-1"></i>
                        </span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Feature 1: Timeline -->
        <section class="relative py-40 overflow-hidden">
             <!-- Decorative Background Text -->
             <div class="absolute top-20 left-0 text-[12rem] font-bold text-white/[0.01] pointer-events-none select-none font-sans z-0 leading-none">
                TIMELINE
            </div>

            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid md:grid-cols-2 gap-24 items-center">
                    <div class="order-2 md:order-1 relative group scroll-reveal-left" :ref="setRef" @mousemove="handleMouseMove($event, $event.currentTarget)" @mouseleave="handleMouseLeave($event.currentTarget)">
                         <!-- Glass Card Effect -->
                         <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-blue-500/5 rounded-2xl blur-3xl opacity-0 group-hover:opacity-40 transition duration-1000 pointer-events-none"></div>
                        <div class="relative overflow-hidden rounded-lg shadow-2xl border border-white/5 group-hover:border-amber-500/30 transition-all duration-100 ease-out transform-gpu">
                             <div class="absolute inset-0 bg-gradient-to-t from-[#0B1120] to-transparent opacity-60 z-10 pointer-events-none"></div>
                            <img
                                src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=1000&auto=format&fit=crop"
                                alt="Timeline"
                                class="relative z-0 grayscale group-hover:grayscale-0 transition-all duration-1000 transform group-hover:scale-110"
                            />
                            <!-- Floating UI Element -->
                            <div class="absolute bottom-8 left-8 z-20 bg-[#0B1120]/60 backdrop-blur-md p-6 border-l-2 border-amber-500">
                                <p class="text-xs text-amber-500 font-bold tracking-widest mb-2">2023.11.22</p>
                                <p class="text-lg font-bold">京都、紅葉のライトアップ</p>
                            </div>
                        </div>
                    </div>

                    <div class="order-1 md:order-2 space-y-10 scroll-reveal-up" :ref="setRef">
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <span class="h-[1px] w-12 bg-amber-500"></span>
                                <span class="text-amber-500 tracking-[0.3em] text-xs font-sans font-bold uppercase"
                                    >01 / Memory</span
                                >
                            </div>
                            <h2 class="text-5xl md:text-6xl font-black leading-tight">
                                色褪せない<br />
                                <span class="text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-500">思い出の軌跡</span>
                            </h2>
                        </div>
                        <p class="text-gray-400 leading-relaxed font-sans font-light text-lg">
                            写真、コメント、そしてその時の感情。<br />
                            タイムライン形式で振り返れば、あの時の空気が鮮やかに蘇ります。
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature 2: Visual Calendar (NEW) -->
        <section class="relative py-40 overflow-hidden">
             <div class="absolute top-40 right-0 text-[12rem] font-bold text-white/[0.01] pointer-events-none select-none font-sans z-0 leading-none text-right">CALENDAR</div>
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid md:grid-cols-2 gap-24 items-center">
                    <div class="space-y-10 scroll-reveal-up" :ref="setRef">
                        <div class="space-y-6">
                            <div class="flex items-center gap-4"><span class="h-[1px] w-12 bg-emerald-500"></span><span class="text-emerald-400 tracking-[0.3em] text-xs font-sans font-bold uppercase">02 / Calendar</span></div>
                            <h2 class="text-5xl md:text-6xl font-black leading-tight">思い出を、<br /><span class="text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-500">時系列で辿る</span></h2>
                        </div>
                        <p class="text-gray-400 leading-relaxed font-sans font-light text-lg">「あの年の夏、どこに行ったっけ？」<br>カレンダー形式なら、過去の旅が一目瞭然。<br>何気ない週末の記録も、大切なピースになります。</p>
                    </div>
                    <div class="relative group scroll-reveal-right perspective-1000" :ref="setRef" @mousemove="handleMouseMove($event, $event.currentTarget)" @mouseleave="handleMouseLeave($event.currentTarget)">
                         <div class="relative bg-[#1E293B]/60 backdrop-blur-md border border-white/10 p-6 rounded-2xl shadow-2xl transform rotate-y-12 rotate-x-6 hover:rotate-0 transition-transform duration-700 ease-out">
                             <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-gray-500 mb-2">
                                 <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                             </div>
                             <div class="grid grid-cols-7 gap-2">
                                 <div v-for="i in 31" :key="i" class="aspect-square rounded-md flex items-center justify-center text-sm relative group/day cursor-pointer hover:bg-white/10 transition-colors">
                                     <span :class="{'text-gray-600': i < 5, 'text-gray-300': i >= 5}">{{ i }}</span>
                                     <div v-if="[12, 18, 25].includes(i)" class="absolute inset-0 m-0.5 rounded-sm overflow-hidden border border-emerald-400/50 shadow-[0_0_10px_rgba(52,211,153,0.4)]">
                                         <img :src="`https://images.unsplash.com/photo-${['1506744038136-46273834b3fb', '1516483638261-f4dbaf036963', '1476514525535-07fb3b4ae5f1'][i%3]}?q=80&w=200&auto=format&fit=crop`" class="w-full h-full object-cover opacity-80 group-hover/day:opacity-100 transition-opacity" />
                                     </div>
                                 </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature 2: Scrapbook (NEW) -->
        <section class="relative py-40 bg-[#0F172A] overflow-hidden">
            <div class="absolute top-20 right-0 text-[12rem] font-bold text-white/[0.01] pointer-events-none select-none font-sans z-0 leading-none text-right">SCRAP</div>
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid md:grid-cols-2 gap-24 items-center">
                    <div class="space-y-10 scroll-reveal-up" :ref="setRef">
                        <div class="space-y-6">
                            <div class="flex items-center gap-4"><span class="h-[1px] w-12 bg-pink-500"></span><span class="text-pink-400 tracking-[0.3em] text-xs font-sans font-bold uppercase">04 / Scrapbook</span></div>
                            <h2 class="text-5xl md:text-6xl font-black leading-tight">心を揺さぶる<br /><span class="text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-500">インスピレーション</span></h2>
                        </div>
                        <p class="text-gray-400 leading-relaxed font-sans font-light text-lg">「いつか行きたい」見つけた絶景やカフェ、気になった記事をスクラップ。<br>次のお出かけのヒントは、あなたの掌の中に。</p>
                    </div>
                    <div class="relative group scroll-reveal-right perspective-1000" :ref="setRef" @mousemove="handleMouseMove($event, $event.currentTarget)" @mouseleave="handleMouseLeave($event.currentTarget)">
                        <!-- Stacked Cards Effect -->
                         <div class="relative w-full aspect-[4/3] flex items-center justify-center transition-all duration-100 ease-out transform-gpu">
                            <div class="absolute w-64 h-80 bg-white p-2 shadow-2xl transform -rotate-12 translate-x-[-40px] translate-y-[20px] transition-all duration-500 group-hover:-rotate-[15deg] group-hover:translate-x-[-60px]">
                                <img src="https://images.unsplash.com/photo-1516483638261-f4dbaf036963?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover rounded-sm filter sepia-[.3]" />
                            </div>
                            <div class="absolute w-64 h-80 bg-white p-2 shadow-2xl transform rotate-6 translate-x-[40px] translate-y-[-20px] transition-all duration-500 group-hover:rotate-[10deg] group-hover:translate-x-[60px] z-10">
                                <img src="https://images.unsplash.com/photo-1551918120-9739cb430c6d?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover rounded-sm filter contrast-125" />
                                <div class="absolute bottom-4 left-0 w-full text-center font-handwriting text-gray-800 text-lg rotate-[-2deg]">Diner in Tokyo</div>
                            </div>
                             <div class="absolute w-64 h-80 bg-white p-2 shadow-xl transform rotate-[-3deg] z-20 transition-all duration-500 group-hover:scale-105">
                                <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover rounded-sm" />
                                <div class="absolute -top-3 -right-3 w-8 h-8 bg-red-500 rounded-full shadow-lg border-2 border-white flex items-center justify-center text-white"><i class="bi bi-heart-fill text-xs"></i></div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature 5: Smart Tags (NEW) -->
        <section class="relative py-40 overflow-hidden bg-[#0B1120]">
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="text-center mb-16 scroll-reveal-up" :ref="setRef">
                    <span class="text-purple-400 tracking-widest text-sm font-sans font-bold uppercase mb-4 block">05 / Organization</span>
                    <h2 class="text-4xl md:text-5xl font-black mb-6">好きに、自由に。<br />タグで管理。</h2>
                </div>
                 <div class="relative h-[400px] w-full flex items-center justify-center overflow-hidden" :ref="setRef">
                      <div class="absolute inset-0 bg-gradient-to-r from-[#0B1120] via-transparent to-[#0B1120] z-10"></div>
                      <div class="flex flex-wrap gap-4 justify-center max-w-4xl mx-auto relative z-0">
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-slow hover:bg-purple-500/20 hover:border-purple-500/50 transition-colors cursor-pointer" style="animation-delay: 0s;">#温泉旅行</span>
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-medium hover:bg-pink-500/20 hover:border-pink-500/50 transition-colors cursor-pointer" style="animation-delay: 1s;">#絶景スポット</span>
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-fast hover:bg-blue-500/20 hover:border-blue-500/50 transition-colors cursor-pointer" style="animation-delay: 2s;">#カフェ巡り</span>
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-slow hover:bg-amber-500/20 hover:border-amber-500/50 transition-colors cursor-pointer" style="animation-delay: 3s;">#記念日デート</span>
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-medium hover:bg-green-500/20 hover:border-green-500/50 transition-colors cursor-pointer" style="animation-delay: 1.5s;">#京都散策</span>
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-fast hover:bg-cyan-500/20 hover:border-cyan-500/50 transition-colors cursor-pointer" style="animation-delay: 2.5s;">#週末ドライブ</span>
                           <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-slow hover:bg-red-500/20 hover:border-red-500/50 transition-colors cursor-pointer" style="animation-delay: 0.5s;">#グルメ</span>
                          <span class="px-6 py-3 rounded-full bg-white/5 border border-white/10 backdrop-blur-md text-gray-300 animate-float-medium hover:bg-indigo-500/20 hover:border-indigo-500/50 transition-colors cursor-pointer" style="animation-delay: 3.5s;">#海外旅行</span>
                      </div>
                 </div>
            </div>
        </section>

        <!-- Feature 3: Map (Fixed) -->
        <section class="relative py-40 overflow-hidden">
             <div class="absolute bottom-20 left-0 text-[12rem] font-bold text-white/[0.01] pointer-events-none select-none font-sans z-0 leading-none">CONQUEST</div>
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid md:grid-cols-2 gap-24 items-center">
                    <div class="relative group scroll-reveal-left" :ref="setRef" @mousemove="handleMouseMove($event, $event.currentTarget)" @mouseleave="handleMouseLeave($event.currentTarget)">
                        <div class="absolute -inset-10 bg-blue-600/20 rounded-full blur-[100px] opacity-20 pointer-events-none"></div>
                        <div class="relative rounded-2xl aspect-video bg-[#1E293B]/50 border border-white/5 backdrop-blur-xl flex items-center justify-center overflow-hidden shadow-2xl group-hover:border-blue-500/30 transition-all duration-100 ease-out transform-gpu">
                             <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
                             <div class="relative z-10 w-full h-full p-10 flex items-center justify-center">
                                  <!-- Fixed Map Image -->
                                  <img src="/images/japan_map.png" class="w-full h-full object-contain opacity-80 drop-shadow-[0_0_15px_rgba(59,130,246,0.3)]" />
                                  <div class="absolute top-[40%] left-[60%]">
                                      <span class="relative flex h-4 w-4">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-500 shadow-[0_0_20px_rgba(59,130,246,1)]"></span>
                                      </span>
                                  </div>
                             </div>
                        </div>
                    </div>
                     <div class="space-y-10 scroll-reveal-up" :ref="setRef">
                        <div class="space-y-6">
                            <div class="flex items-center gap-4"><span class="h-[1px] w-12 bg-blue-500"></span><span class="text-blue-400 tracking-[0.3em] text-xs font-sans font-bold uppercase">03 / Map</span></div>
                            <h2 class="text-5xl md:text-6xl font-black leading-tight">ふたりで埋める<br /><span class="text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-500">日本の地図</span></h2>
                        </div>
                         <p class="text-gray-400 leading-relaxed font-sans font-light text-lg">訪れた都道府県が色づいていく喜び。<br />地図を見ながら「次はここ！」と盛り上がる時間。</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Feature 4: Achievements/Badges (NEW) -->
         <section class="relative py-40 bg-[#0F172A] overflow-hidden">
             <div class="absolute top-20 right-0 text-[12rem] font-bold text-white/[0.01] pointer-events-none select-none font-sans z-0 leading-none text-right">GLORY</div>
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid md:grid-cols-2 gap-24 items-center">
                    <div class="space-y-10 scroll-reveal-up" :ref="setRef">
                         <div class="space-y-6">
                            <div class="flex items-center gap-4"><span class="h-[1px] w-12 bg-yellow-500"></span><span class="text-yellow-400 tracking-[0.3em] text-xs font-sans font-bold uppercase">07 / Badges</span></div>
                            <h2 class="text-5xl md:text-6xl font-black leading-tight">旅の証を、<br /><span class="text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-500">勲章に変えて</span></h2>
                        </div>
                        <p class="text-gray-400 leading-relaxed font-sans font-light text-lg">「初めての旅行」「10都道府県制覇」「温泉巡り」。<br>ふたりのマイルストーンを、輝くバッジとしてコレクション。</p>
                    </div>
                     <div class="relative group scroll-reveal-right" :ref="setRef" @mousemove="handleMouseMove($event, $event.currentTarget)" @mouseleave="handleMouseLeave($event.currentTarget)">
                        <!-- Badge Showcase -->
                         <div class="grid grid-cols-2 gap-6 relative z-10 transition-all duration-100 ease-out transform-gpu">
                             <div class="bg-[#1E293B]/60 backdrop-blur border border-yellow-500/20 p-6 rounded-xl flex flex-col items-center gap-4 hover:bg-[#1E293B] hover:border-yellow-500/50 transition-all shadow-xl">
                                 <div class="w-16 h-16 rounded-full bg-gradient-to-br from-yellow-300 via-yellow-500 to-yellow-700 shadow-lg flex items-center justify-center text-white text-2xl animate-shine overflow-hidden relative">
                                     <i class="bi bi-trophy-fill relative z-10"></i>
                                     <div class="absolute top-0 -left-[100%] w-full h-full bg-gradient-to-r from-transparent via-white/50 to-transparent skew-x-[-25deg] animate-shine-sweep"></div>
                                 </div>
                                 <div class="text-center"><p class="font-bold text-yellow-500 text-sm tracking-widest">FIRST TRIP</p><p class="text-xs text-gray-500 font-sans">2023.05.01</p></div>
                             </div>
                              <div class="bg-[#1E293B]/60 backdrop-blur border border-gray-700 p-6 rounded-xl flex flex-col items-center gap-4 opacity-70 grayscale hover:grayscale-0 hover:opacity-100 hover:border-blue-400 transition-all shadow-xl">
                                 <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-300 via-blue-500 to-blue-700 shadow-lg flex items-center justify-center text-white text-2xl border-4 border-gray-800">
                                     <i class="bi bi-airplane-fill"></i>
                                 </div>
                                  <div class="text-center"><p class="font-bold text-gray-400 text-sm tracking-widest">OVERSEAS</p><p class="text-xs text-gray-600 font-sans">Locked</p></div>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
         </section>

        <!-- Feature 5: AI Planner -->
        <section class="relative py-40 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid md:grid-cols-2 gap-24 items-center">
                    <div class="order-2 md:order-1 relative group scroll-reveal-left" :ref="setRef" @mousemove="handleMouseMove($event, $event.currentTarget)" @mouseleave="handleMouseLeave($event.currentTarget)">
                         <div class="absolute -inset-4 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg blur-2xl opacity-0 group-hover:opacity-100 transition duration-1000 pointer-events-none"></div>
                        <div class="relative rounded-sm p-8 bg-[#1E293B]/50 backdrop-blur-sm border border-white/10 shadow-2xl transition-all duration-100 ease-out transform-gpu">
                            <div class="space-y-4 font-sans">
                                <div class="flex items-start gap-4">
                                    <div class="w-8 h-8 rounded-full bg-gray-600 flex-shrink-0"></div>
                                    <div class="bg-gray-700 rounded-lg rounded-tl-none p-3 text-sm text-gray-200">今週末、どこか温泉に行きたいな。おすすめある？</div>
                                </div>
                                <div class="flex items-start gap-4 flex-row-reverse">
                                    <div class="w-8 h-8 rounded-full bg-amber-600 flex flex-col items-center justify-center text-[10px] text-white font-bold">AI</div>
                                    <div class="bg-amber-900/40 border border-amber-500/20 rounded-lg rounded-tr-none p-3 text-sm text-gray-200">
                                        それなら、箱根はいかがですか？🍁<br>おすすめのプランを作成しました！
                                    </div>
                                </div>
                                <div class="mt-4 p-4 rounded bg-[#0B1120] border border-white/5 flex justify-between items-center hover:bg-[#151c2f] cursor-pointer transition-colors">
                                    <span class="text-sm font-bold text-amber-500">箱根・大涌谷めぐり</span>
                                    <i class="bi bi-chevron-right text-gray-500"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-1 md:order-2 space-y-8 scroll-reveal-up" :ref="setRef">
                        <div class="space-y-4">
                            <span class="text-purple-400 tracking-widest text-sm font-sans font-bold">06 / AI PLANNER</span>
                            <h2 class="text-4xl md:text-5xl font-bold leading-tight">次の旅も、<br />スムーズに</h2>
                        </div>
                        <p class="text-gray-400 leading-relaxed font-sans font-light">「どこ行く？」で喧嘩するのはもう終わり。<br />AI専属プランナーが、ふたりの好みに合わせた最高の旅をご提案します。</p>
                    </div>
                </div>
            </div>
        </section>


        <!-- Feature 8: Statistics (NEW) -->
         <section class="py-24 border-y border-white/5 bg-[#0F172A]/50 backdrop-blur">
             <div class="max-w-7xl mx-auto px-6">
                 <div class="grid grid-cols-2 md:grid-cols-4 gap-12 text-center">
                     <div class="space-y-2 group cursor-default">
                         <div class="text-4xl md:text-5xl font-black text-white group-hover:text-amber-500 transition-colors duration-300">12</div>
                         <div class="text-xs tracking-[0.2em] text-gray-500 uppercase">Trips</div>
                     </div>
                      <div class="space-y-2 group cursor-default">
                         <div class="text-4xl md:text-5xl font-black text-white group-hover:text-blue-500 transition-colors duration-300">47</div>
                         <div class="text-xs tracking-[0.2em] text-gray-500 uppercase">Prefectures</div>
                     </div>
                      <div class="space-y-2 group cursor-default">
                         <div class="text-4xl md:text-5xl font-black text-white group-hover:text-pink-500 transition-colors duration-300">1,402</div>
                         <div class="text-xs tracking-[0.2em] text-gray-500 uppercase">Photos</div>
                     </div>
                      <div class="space-y-2 group cursor-default">
                         <div class="text-4xl md:text-5xl font-black text-white group-hover:text-purple-500 transition-colors duration-300">∞</div>
                         <div class="text-xs tracking-[0.2em] text-gray-500 uppercase">Memories</div>
                     </div>
                 </div>
             </div>
         </section>

        <!-- CTA Section -->
        <section class="py-40 relative overflow-hidden flex items-center justify-center min-h-[60vh]">
             <div class="absolute inset-0 z-0 bg-fixed bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1534067783865-61267555638e?q=80&w=2069&auto=format&fit=crop'); filter: grayscale(100%);">
                <div class="absolute inset-0 bg-[#0B1120]/80"></div>
            </div>
            <div class="max-w-4xl mx-auto text-center px-6 relative z-10 scroll-reveal-up" :ref="setRef">
                <i class="bi bi-stars text-amber-500 text-4xl mb-8 block animate-pulse"></i>
                <h2 class="text-5xl md:text-7xl font-black mb-10 tracking-tight">さあ、物語の続きを。</h2>
                <Link :href="route('register')" class="relative inline-flex group items-center justify-center px-12 py-6 overflow-hidden font-medium text-amber-950 transition duration-300 ease-out border-2 border-amber-500 rounded-sm shadow-md">
                    <span class="absolute inset-0 flex items-center justify-center w-full h-full text-white duration-300 -translate-x-full bg-amber-600 group-hover:translate-x-0 ease"><i class="bi bi-arrow-right text-xl"></i></span>
                    <span class="absolute flex items-center justify-center w-full h-full text-amber-500 transition-all duration-300 transform group-hover:translate-x-full ease font-bold tracking-[0.2em] uppercase">Start for Free</span>
                    <span class="relative invisible">Start for Free</span>
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#050911] border-t border-white/5 py-12 relative z-10">
            <div class="max-w-7xl mx-auto px-6 text-center text-gray-700 text-xs font-sans tracking-widest">
                <div class="flex items-center justify-center gap-2 mb-6 opacity-30 hover:opacity-100 transition-opacity duration-500"><i class="bi bi-journal-album text-white text-lg"></i><span class="font-bold text-gray-400">OMOiDE ALBUM</span></div>
                &copy; 2025 Omoide Album. All rights reserved.
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Keyframes */
@keyframes kenBurns { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }
@keyframes drop { 0% { transform: translateY(-100%); } 100% { transform: translateY(100%); } }
@keyframes shimmer { 0% { background-position: 200% center; } 100% { background-position: -200% center; } }
@keyframes shineCheck { 0% { left: -100%; } 100% { left: 100%; } }

/* Classes */
.animate-ken-burns { animation: kenBurns 20s alternate infinite linear; }
.animate-drop { animation: drop 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
.animate-shimmer { animation: shimmer 8s linear infinite; }
.animate-shine-sweep { animation: shineCheck 3s infinite linear; }

.animate-fade-in-down {
    animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(-30px);
}

.animate-fade-in-up {
    animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(30px);
}

@keyframes fadeInDown { to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }

/* Scroll Reveal States */
.scroll-reveal-up { opacity: 0; transform: translateY(60px); transition: all 1s cubic-bezier(0.16, 1, 0.3, 1); }
.scroll-reveal-left { opacity: 0; transform: translateX(-40px); transition: all 1s cubic-bezier(0.16, 1, 0.3, 1); }
.scroll-reveal-right { opacity: 0; transform: translateX(40px); transition: all 1s cubic-bezier(0.16, 1, 0.3, 1); }

.is-visible {
    opacity: 1 !important;
    transform: translate(0, 0) !important;
}

.delay-200 { animation-delay: 0.2s; transition-delay: 0.1s; }
.delay-400 { animation-delay: 0.4s; transition-delay: 0.2s; }
.delay-500 { animation-delay: 0.5s; transition-delay: 0.3s; }

.fill-mode-forwards {
    animation-fill-mode: forwards;
}

/* Perspective for 3D tilts if added later */
.perspective-1000 {
    perspective: 1000px;
}

.font-handwriting { font-family: 'Cedarville Cursive', cursive; }

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
.animate-float-slow { animation: float 6s ease-in-out infinite; }
.animate-float-medium { animation: float 5s ease-in-out infinite; }
.animate-float-fast { animation: float 4s ease-in-out infinite; }
</style>
```
