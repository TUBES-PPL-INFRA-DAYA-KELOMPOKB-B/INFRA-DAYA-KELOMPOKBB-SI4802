<x-warga-layout title="Portal Pelaporan Kerusakan Infrastruktur Kota Bandung">
    <!-- HERO SECTION (Gedung Sate Bandung Landmark Background) -->
    <div class="relative bg-[#070B14] text-white border-b border-white/10 overflow-hidden">
        <!-- Background Image: Gedung Sate Bandung -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <img 
                src="{{ asset('images/gedung-sate-bandung.jpg') }}" 
                alt="Ikon Gedung Sate Bandung" 
                class="w-full h-full object-cover object-[center_35%] filter contrast-105 brightness-95"
            >
            <!-- Intelligent Directional Gradient Overlays: Deep contrast on the left for text, clear view of Gedung Sate on the right -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#070B14] via-[#070B14]/80 via-45% to-[#070B14]/25"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#070B14] via-transparent via-60% to-[#070B14]/70"></div>
            <div class="absolute inset-0 bg-radial-[at_80%_40%] from-blue-600/10 via-transparent to-transparent"></div>
        </div>

        <!-- Radial Ambient Glow on the right side -->
        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-blue-600/20 rounded-full blur-[140px] pointer-events-none z-1"></div>
        <div class="absolute -left-40 -top-40 w-[500px] h-[500px] bg-indigo-600/15 rounded-full blur-[120px] pointer-events-none z-1"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                <!-- Left Column: High-Authority Civic & Technical Statement -->
                <div class="lg:col-span-7 space-y-7">
                    <!-- Institutional Badges (Exact Sentry Tag Style) -->
                    <div class="inline-flex flex-wrap items-center gap-2.5 font-mono text-[11px]">
                        <span class="px-2.5 py-1 rounded bg-white/[0.06] border border-white/15 uppercase tracking-widest text-slate-300 font-bold">
                            SDG 9 KOTA BANDUNG
                        </span>
                        <span class="px-2.5 py-1 rounded bg-blue-500/10 border border-blue-500/30 text-blue-400 font-semibold flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            STATUS: SIAGA AKTIF URC DSDABM
                        </span>
                    </div>

                    <!-- Enormous, Razor-sharp Headline -->
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.05]">
                        Investigasi cepat, <br>
                        <span class="text-slate-100">tuntas sebelum meluas</span>
                    </h1>

                    <!-- Technical Subtitle -->
                    <p class="text-base sm:text-lg text-slate-300 max-w-xl leading-relaxed">
                        Sistem komando dan pelaporan infrastruktur publik terpadu <strong class="text-white font-bold">INFRA-DAYA &bull; DSDABM Pemerintah Kota Bandung</strong>. Navigasi dan selesaikan kerusakan jalan berlubang, saluran air tersumbat, jembatan, serta lampu jalan secara geospasial real-time.
                    </p>

                    <!-- Direct Action Controls (Button + Quick Ticket Tracker Input) -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 max-w-xl">
                        <!-- Primary CTA Button (Blue with Arrow) -->
                        <a
                            href="{{ route('warga.laporan.create', [], false) }}"
                            class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white px-6 py-3.5 text-sm font-bold shadow-lg shadow-blue-600/30 transition-all cursor-pointer whitespace-nowrap group"
                        >
                            <span>Lapor Kerusakan</span>
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>

                        <!-- Direct Ticket Tracker Console Form -->
                        <form action="{{ route('tracking.publik', [], false) }}" method="GET" class="flex-1 relative flex items-center">
                            <input
                                type="text"
                                name="nomor_tiket"
                                placeholder="Lacak Tiket: INFRA-2026..."
                                class="w-full rounded-xl bg-white/[0.08] hover:bg-white/[0.12] border border-white/20 px-4 py-3 text-sm text-white placeholder-slate-400 font-mono focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors uppercase font-bold"
                            >
                            <button
                                type="submit"
                                class="absolute right-1.5 px-3.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors cursor-pointer"
                            >
                                Lacak Tiket
                            </button>
                        </form>
                    </div>

                    <!-- Civic Trust Data Line -->
                    <div class="pt-5 border-t border-white/10 flex flex-wrap items-center gap-6 sm:gap-10 text-xs text-slate-300 font-mono">
                        <div>
                            <span class="text-white font-bold text-sm block">30 Kecamatan</span>
                            <span class="text-slate-400 text-[11px]">Cakupan Wilayah</span>
                        </div>
                        <div class="border-l border-white/10 pl-6">
                            <span class="text-blue-400 font-bold text-sm block">&lt; 24 Jam</span>
                            <span class="text-slate-400 text-[11px]">Standar Verifikasi SLA</span>
                        </div>
                        <div class="border-l border-white/10 pl-6">
                            <span class="text-emerald-400 font-bold text-sm block">100% Terbuka</span>
                            <span class="text-slate-400 text-[11px]">Transparansi Publik</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: 3D Isometric Telemetry Platform Graphic (Exact Roobinium Style) -->
                <div class="lg:col-span-5 relative flex items-center justify-center">
                    <div class="w-full max-w-[540px] aspect-[1.15] relative select-none">
                        <!-- High-Fidelity SVG Isometric Wireframe Platform -->
                        <svg viewBox="0 0 600 520" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-2xl">
                            <!-- Definitions for Gradients -->
                            <defs>
                                <radialGradient id="platformGlow" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.35"/>
                                    <stop offset="70%" stop-color="#1D4ED8" stop-opacity="0.1"/>
                                    <stop offset="100%" stop-color="#070B14" stop-opacity="0"/>
                                </radialGradient>
                                <linearGradient id="planeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#1E293B" stop-opacity="0.85"/>
                                    <stop offset="100%" stop-color="#0F172A" stop-opacity="0.95"/>
                                </linearGradient>
                                <linearGradient id="edgeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#60A5FA"/>
                                    <stop offset="100%" stop-color="#2563EB"/>
                                </linearGradient>
                                <linearGradient id="cyanGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.8"/>
                                    <stop offset="100%" stop-color="#0284C7" stop-opacity="0.2"/>
                                </linearGradient>
                            </defs>

                            <!-- Ambient Center Glow Behind Slab -->
                            <ellipse cx="300" cy="340" rx="260" ry="140" fill="url(#platformGlow)"/>

                            <!-- BASE ISOMETRIC SLAB -->
                            <!-- Slab Thickness Left -->
                            <path d="M 80,310 L 300,430 L 300,455 L 80,335 Z" fill="#1E3A8A" opacity="0.65"/>
                            <!-- Slab Thickness Right -->
                            <path d="M 300,430 L 520,310 L 520,335 L 300,455 Z" fill="#172554" opacity="0.85"/>
                            <!-- Slab Top Face -->
                            <path d="M 300,190 L 520,310 L 300,430 L 80,310 Z" fill="url(#planeGrad)" stroke="url(#edgeGrad)" stroke-width="1.8"/>

                            <!-- ISOMETRIC INTERNAL GRID LINES (Municipal Infrastructure Grid) -->
                            <g stroke="#3B82F6" stroke-opacity="0.25" stroke-width="1" stroke-dasharray="3 3">
                                <!-- Horizontal Iso Grid -->
                                <line x1="135" y1="280" x2="355" y2="400"/>
                                <line x1="190" y1="250" x2="410" y2="370"/>
                                <line x1="245" y1="220" x2="465" y2="340"/>
                                <!-- Cross Iso Grid -->
                                <line x1="465" y1="280" x2="245" y2="400"/>
                                <line x1="410" y1="250" x2="190" y2="370"/>
                                <line x1="355" y1="220" x2="135" y2="340"/>
                            </g>

                            <!-- ELEVATED ISOMETRIC WIREFRAME BOX 1: ALERT TRENDS (Exact Sentry Visual) -->
                            <!-- Vertical Projection Lines -->
                            <line x1="250" y1="160" x2="250" y2="310" stroke="#60A5FA" stroke-opacity="0.4" stroke-width="1.2" stroke-dasharray="2 2"/>
                            <line x1="470" y1="160" x2="470" y2="310" stroke="#60A5FA" stroke-opacity="0.4" stroke-width="1.2" stroke-dasharray="2 2"/>
                            <line x1="360" y1="95" x2="360" y2="245" stroke="#60A5FA" stroke-opacity="0.3" stroke-width="1.2" stroke-dasharray="2 2"/>
                            <line x1="360" y1="225" x2="360" y2="375" stroke="#60A5FA" stroke-opacity="0.4" stroke-width="1.2" stroke-dasharray="2 2"/>

                            <!-- Floating Wireframe Top Box -->
                            <path d="M 360,95 L 470,160 L 360,225 L 250,160 Z" fill="url(#cyanGrad)" stroke="#60A5FA" stroke-width="1.8"/>
                            <path d="M 250,160 L 360,225 L 360,255 L 250,190 Z" fill="#0369A1" opacity="0.3" stroke="#38BDF8" stroke-width="1"/>
                            <path d="M 360,225 L 470,160 L 470,190 L 360,255 Z" fill="#075985" opacity="0.4" stroke="#38BDF8" stroke-width="1"/>

                            <!-- Top Trend Curve / Data Waves inside Floating Wireframe Box -->
                            <path d="M 270,175 Q 320,130 360,160 T 450,135" fill="none" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round"/>
                            <circle cx="360" cy="160" r="4" fill="#60A5FA" stroke="#FFFFFF" stroke-width="1.5"/>
                            <circle cx="450" cy="135" r="3.5" fill="#38BDF8"/>
                            <circle cx="270" cy="175" r="3" fill="#93C5FD"/>

                            <!-- Wireframe Label 1: ALERT TRENDS -->
                            <text x="360" y="80" text-anchor="middle" font-family="Inter, monospace" font-size="10" font-weight="800" fill="#93C5FD" letter-spacing="2">
                                ALERT TRENDS &bull; RESPONS CEPAT URC
                            </text>
                            <text x="360" y="145" text-anchor="middle" font-family="Inter, sans-serif" font-size="9" font-weight="600" fill="#E2E8F0" opacity="0.8">
                                Mengukur kecepatan perbaikan 30 kecamatan
                            </text>

                            <!-- FLOATING ISOMETRIC TAG 2: SECURITY & SLA BENCHMARK (Bottom Left) -->
                            <g transform="translate(110, 260)">
                                <!-- Small Iso Diamond -->
                                <path d="M 120,40 L 190,80 L 120,120 L 50,80 Z" fill="#0F172A" fill-opacity="0.8" stroke="#3B82F6" stroke-width="1.5"/>
                                <text x="120" y="84" text-anchor="middle" font-family="Inter, monospace" font-size="9" font-weight="800" fill="#60A5FA" letter-spacing="1">
                                    SLA VERIFIKASI &lt;24 JAM
                                </text>
                            </g>

                            <!-- Coordinate Nodes with Radar Pulses on the Base Plane -->
                            <!-- Node 1: Bandung Wetan -->
                            <circle cx="280" cy="300" r="5" fill="#38BDF8"/>
                            <circle cx="280" cy="300" r="14" stroke="#38BDF8" stroke-width="1" opacity="0.6"/>
                            <!-- Node 2: Pasteur / Sukajadi -->
                            <circle cx="220" cy="335" r="4" fill="#60A5FA"/>
                            <!-- Node 3: Ujungberung -->
                            <circle cx="380" cy="330" r="4.5" fill="#F59E0B"/>
                            <circle cx="380" cy="330" r="12" stroke="#F59E0B" stroke-width="1" opacity="0.5"/>
                            <!-- Node 4: Gedebage -->
                            <circle cx="330" cy="380" r="4" fill="#10B981"/>

                            <!-- Connecting Laser Data Lines -->
                            <line x1="220" y1="335" x2="280" y2="300" stroke="#60A5FA" stroke-width="1.2" opacity="0.7"/>
                            <line x1="280" y1="300" x2="380" y2="330" stroke="#38BDF8" stroke-width="1.2" opacity="0.7"/>
                            <line x1="280" y1="300" x2="330" y2="380" stroke="#10B981" stroke-width="1.2" opacity="0.7"/>

                            <!-- Telemetry Coordinate Annotation -->
                            <text x="300" y="485" text-anchor="middle" font-family="Inter, monospace" font-size="10" font-weight="700" fill="#64748B" letter-spacing="1.5">
                                GEO TELEMETRI: -6.9175, 107.6191 &bull; DSDABM KOTA BANDUNG
                            </text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: 4-COLUMN ISOMETRIC INFRASTRUCTURE MODULES (Screenshot 2 Style) -->
    <section class="bg-[#F8FAFC] py-20 lg:py-28 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header (Exact Sentry Typography Style) -->
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20">
                <h2 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.1]">
                    Dirancang untuk aduan <br>
                    <span class="text-blue-600">yang benar-benar krusial</span>
                </h2>
                <p class="mt-4 text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto">
                    INFRA-DAYA menyaring dan memvalidasi laporan warga agar Unit Reaksi Cepat (URC) DSDABM fokus menyelesaikan titik kerusakan yang paling mendesak di Kota Bandung.
                </p>
            </div>

            <!-- 4-Column Architectural Wireframe Grid (LOG 01 - LOG 04) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- CARD 1: LOG 01 - Jalan Raya & Trotoar -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col justify-between shadow-xs hover:shadow-md hover:border-blue-400 transition-all duration-300 group">
                    <div>
                        <!-- Header Label -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="font-mono text-xs font-bold text-slate-400 tracking-wider">LOG 01</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                SLA: 3 Hari
                            </span>
                        </div>

                        <!-- Real Photo: Jalan Raya & Trotoar -->
                        <div class="my-5 aspect-[16/10] relative rounded-xl border border-slate-200/90 overflow-hidden bg-slate-100 shadow-xs">
                            <img src="{{ asset('images/service-jalan.jpg') }}" 
                                 alt="Infrastruktur Jalan Raya & Trotoar Kota Bandung" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/30 via-transparent to-transparent opacity-60 group-hover:opacity-20 transition-opacity"></div>
                        </div>

                        <!-- Card Meta & Content -->
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Jalan Raya &amp; Trotoar
                        </h3>
                        <p class="text-xs font-semibold text-blue-600 mt-0.5">
                            Divisi Bina Marga Kota Bandung
                        </p>
                        <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                            Penanganan lubang aspal amblas, retak struktural, marka luntur, serta kerusakan paving trotoar pejalan kaki.
                        </p>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-400">Prioritas: Tinggi</span>
                        <a href="{{ route('warga.laporan.create', ['kategori' => 'jalan'], false) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                            Lapor &rarr;
                        </a>
                    </div>
                </div>

                <!-- CARD 2: LOG 02 - Drainase & Saluran Air -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col justify-between shadow-xs hover:shadow-md hover:border-blue-400 transition-all duration-300 group">
                    <div>
                        <!-- Header Label -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="font-mono text-xs font-bold text-slate-400 tracking-wider">LOG 02</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                SLA: 2 Hari
                            </span>
                        </div>

                        <!-- Real Photo: Drainase & Saluran Air -->
                        <div class="my-5 aspect-[16/10] relative rounded-xl border border-slate-200/90 overflow-hidden bg-slate-100 shadow-xs">
                            <img src="{{ asset('images/service-drainase.jpg') }}" 
                                 alt="Infrastruktur Drainase & Saluran Air Kota Bandung" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/30 via-transparent to-transparent opacity-60 group-hover:opacity-20 transition-opacity"></div>
                        </div>

                        <!-- Card Meta & Content -->
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Drainase &amp; Saluran Air
                        </h3>
                        <p class="text-xs font-semibold text-cyan-600 mt-0.5">
                            Divisi Sumber Daya Air (SDA)
                        </p>
                        <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                            Normalisasi sumbatan gorong-gorong, sedimentasi lumpur, genangan banjir musiman, serta keretakan dinding kirmir.
                        </p>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-400">Prioritas: Siaga</span>
                        <a href="{{ route('warga.laporan.create', ['kategori' => 'drainase'], false) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                            Lapor &rarr;
                        </a>
                    </div>
                </div>

                <!-- CARD 3: LOG 03 - Penerangan Jalan Umum -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col justify-between shadow-xs hover:shadow-md hover:border-blue-400 transition-all duration-300 group">
                    <div>
                        <!-- Header Label -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="font-mono text-xs font-bold text-slate-400 tracking-wider">LOG 03</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                SLA: 24 Jam
                            </span>
                        </div>

                        <!-- Real Photo: Penerangan Jalan Umum (PJU) -->
                        <div class="my-5 aspect-[16/10] relative rounded-xl border border-slate-200/90 overflow-hidden bg-slate-100 shadow-xs">
                            <img src="{{ asset('images/service-pju.jpg') }}" 
                                 alt="Infrastruktur Penerangan Jalan Umum (PJU) Kota Bandung" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/30 via-transparent to-transparent opacity-60 group-hover:opacity-20 transition-opacity"></div>
                        </div>

                        <!-- Card Meta & Content -->
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Penerangan Jalan Umum
                        </h3>
                        <p class="text-xs font-semibold text-amber-600 mt-0.5">
                            Divisi Kelistrikan &amp; Utilitas PJU
                        </p>
                        <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                            Lampu penerangan padam malam hari, tiang miring tersenggol, kabel menjuntai bahaya, dan konsleting utilitas.
                        </p>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-400">Prioritas: Mendesak</span>
                        <a href="{{ route('warga.laporan.create', ['kategori' => 'pju'], false) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                            Lapor &rarr;
                        </a>
                    </div>
                </div>

                <!-- CARD 4: LOG 04 - Jembatan & Tanggul -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col justify-between shadow-xs hover:shadow-md hover:border-blue-400 transition-all duration-300 group">
                    <div>
                        <!-- Header Label -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="font-mono text-xs font-bold text-slate-400 tracking-wider">LOG 04</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                SLA: 5 Hari
                            </span>
                        </div>

                        <!-- Real Photo: Jembatan & Tanggul Air -->
                        <div class="my-5 aspect-[16/10] relative rounded-xl border border-slate-200/90 overflow-hidden bg-slate-100 shadow-xs">
                            <img src="{{ asset('images/service-jembatan.jpg') }}" 
                                 alt="Infrastruktur Jembatan & Tanggul Kota Bandung" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                                 loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/30 via-transparent to-transparent opacity-60 group-hover:opacity-20 transition-opacity"></div>
                        </div>

                        <!-- Card Meta & Content -->
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">
                            Jembatan &amp; Tanggul
                        </h3>
                        <p class="text-xs font-semibold text-indigo-600 mt-0.5">
                            Tim Rekayasa Jembatan DSDABM
                        </p>
                        <p class="text-xs text-slate-500 mt-2.5 leading-relaxed">
                            Inspeksi retak gelagar jembatan, expansion joint renggang, oprit amblas, serta penguatan tanggul sungai penahan longsor.
                        </p>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-400">Prioritas: Struktural</span>
                        <a href="{{ route('warga.laporan.create', ['kategori' => 'jembatan'], false) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                            Lapor &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: INCIDENT TELEMETRY & LIVE PUBLIC MONITORING (Screenshot 3 Style) -->
    <section class="bg-white py-20 lg:py-28 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header (Exact Sentry Typography Style) -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.1]">
                    Warga &amp; tim teknis memantau <br>
                    <span class="text-blue-600">setiap sinyal perbaikan</span>
                </h2>
                <p class="mt-4 text-sm sm:text-base text-slate-600 leading-relaxed max-w-xl mx-auto">
                    Transparansi pelacakan menyeluruh dari laporan diterima, verifikasi admin, pengerjaan tim URC, hingga foto berita acara tuntas di lapangan.
                </p>
                <div class="mt-4">
                    <a href="{{ route('tracking.publik', [], false) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 tracking-wide uppercase font-mono">
                        <span>Lacak Tiket Sekarang</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- 3 High-Fidelity UI Previews (Screenshot 3 Card Style) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- PREVIEW 1: Interactive Leaflet GIS Map Console (7 cols) -->
                <div class="lg:col-span-7 bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-xl text-white">
                    <!-- Top Map Window Bar -->
                    <div class="bg-slate-950 px-5 py-3.5 border-b border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <span class="ml-3 font-mono text-xs font-semibold text-slate-300">
                                PETA SEBARAN PAN-BANDUNG &bull; GIS RADAR
                            </span>
                        </div>
                        <a href="{{ route('peta.publik', [], false) }}" class="text-xs font-semibold text-blue-400 hover:text-blue-300">
                            Buka Layar Penuh &rarr;
                        </a>
                    </div>

                    <!-- Live Leaflet Map Container -->
                    <div id="home-preview-map" class="h-80 sm:h-96 w-full z-0"></div>

                    <!-- Bottom Map Filter Bar -->
                    <div class="p-4 bg-slate-950/80 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
                        <div class="flex items-center gap-2 font-mono text-[11px]">
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Jalan</span>
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-cyan-400"></span> Drainase</span>
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-400"></span> PJU</span>
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Tuntas</span>
                        </div>
                        <span class="font-mono text-[11px] text-slate-300">30 Kecamatan Terpadu DSDABM</span>
                    </div>
                </div>

                <!-- PREVIEW 2 & 3: Telemetry Digest Cards (5 cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- CARD A: Live Digest Feed (Screenshot 3 "Weekly Threat Digest" Style) -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Status Terkini</span>
                                <h3 class="text-sm font-bold text-slate-900 mt-0.5">Log Pengaduan Terverifikasi</h3>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                LIVE STREAM
                            </span>
                        </div>

                        <!-- Incident Digest Items -->
                        <div class="divide-y divide-slate-100 mt-3 space-y-3">
                            <div class="pt-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-blue-600">INFRA-20261014-0012</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        URC Dikerjakan
                                    </span>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 mt-1">Aspal Amblas &bull; Jl. Asia Afrika, Kec. Sumur Bandung</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tim URC Bina Marga sedang melakukan patching hotmix</p>
                            </div>

                            <div class="pt-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-blue-600">INFRA-20261014-0008</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai 100%
                                    </span>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 mt-1">Sumbatan Gorong-gorong &bull; Jl. Dr. Djunjunan (Pasteur)</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Normalisasi sedimen tuntas, air mengalir lancar</p>
                            </div>

                            <div class="pt-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs font-bold text-blue-600">INFRA-20261014-0004</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Disposisi URC
                                    </span>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 mt-1">Lampu PJU Padam &bull; Jl. Supratman, Kec. Cibeunying Kaler</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Surat tugas regu teknisi kelistrikan telah diterbitkan</p>
                            </div>
                        </div>
                    </div>

                    <!-- CARD B: Telemetry Metrics & SLA Response Score -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Efektivitas SOP</span>
                                <h3 class="text-sm font-bold text-slate-900 mt-0.5">Kinerja Tanggap DSDABM</h3>
                            </div>
                            <span class="text-xs font-bold text-blue-600 font-mono">TAHUN 2026</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-2xl font-black text-slate-900 block">&lt; 24 Jam</span>
                                <span class="text-[11px] text-slate-500 font-medium">Kecepatan Verifikasi</span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-2xl font-black text-emerald-600 block">98.4%</span>
                                <span class="text-[11px] text-slate-500 font-medium">Tepat Standar SLA</span>
                            </div>
                        </div>

                        <!-- 30 Kecamatan Heatmap Dot Matrix -->
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-2">
                                <span>Radar Status 30 Kecamatan</span>
                                <span class="text-emerald-600 font-bold">100% Aktif</span>
                            </div>
                            <div class="grid grid-cols-10 gap-1.5">
                                @for ($i = 0; $i < 30; $i++)
                                    <div class="h-2 rounded-sm bg-blue-500/80 hover:bg-blue-600 transition-colors" title="Kecamatan Aktif"></div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: 4-STEP ENGINEERING PROTOCOL (SOP DSDABM) -->
    <section class="bg-[#F8FAFC] py-20 lg:py-24 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-blue-600">Standar Operasional Prosedur</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1">
                    4 Langkah Penanganan Terpadu
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Dari sinyal laporan warga hingga berita acara foto tuntas di lapangan
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative">
                    <span class="font-mono text-2xl font-extrabold text-slate-300">01</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-2">Sinyal Laporan Warga</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Pengaduan dikirimkan lengkap dengan foto bukti kerusakan dan titik koordinat GPS akurat.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative">
                    <span class="font-mono text-2xl font-extrabold text-blue-500">02</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-2">Verifikasi DSDABM</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Admin memvalidasi keabsahan data, menetapkan skala prioritas, dan menerbitkan nomor tiket resmi.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative">
                    <span class="font-mono text-2xl font-extrabold text-amber-500">03</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-2">Disposisi Tim URC</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Surat tugas otomatis diteruskan ke petugas teknis lapangan untuk perbaikan fisik sesuai SLA.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative">
                    <span class="font-mono text-2xl font-extrabold text-emerald-500">04</span>
                    <h3 class="text-sm font-bold text-slate-900 mt-2">Dokumentasi Tuntas</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Foto perbaikan selesai dipublikasikan secara terbuka dan pelapor dapat memberikan ulasan rating.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: CALL TO ACTION BANNER (Closing Deck) -->
    <section class="bg-[#070B14] py-16 lg:py-20 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-blue-600/10 pointer-events-none"></div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-6">
            <span class="px-3 py-1 rounded bg-white/[0.08] border border-white/15 text-xs font-mono font-bold text-blue-400 uppercase tracking-widest">
                KOTA BANDUNG SIAGA INFRASTRUKTUR
            </span>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                Wujudkan Bandung Lebih Nyaman dan Tanggap
            </h2>
            <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Partisipasi aktif Anda mempercepat perbaikan fasilitas publik kota. Kirimkan laporan atau pantau sebaran masalah di sekitar Anda.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a
                    href="{{ route('warga.laporan.create', [], false) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white px-7 py-3.5 text-sm font-bold shadow-lg shadow-blue-600/30 transition-all cursor-pointer"
                >
                    <span>Lapor Kerusakan Baru</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a
                    href="{{ route('peta.publik', [], false) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-white/[0.08] hover:bg-white/[0.15] border border-white/20 text-white px-7 py-3.5 text-sm font-bold transition-all"
                >
                    <span>Buka Peta Publik</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Leaflet Script for Home Map -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapElement = document.getElementById('home-preview-map');
            if (!mapElement) return;

            // Titik Pusat Kota Bandung (Kantor DSDABM Jl. Cianjur No. 34)
            const bandungCenter = [-6.9175, 107.6191];
            const map = L.map('home-preview-map', {
                scrollWheelZoom: false,
                attributionControl: false
            }).setView(bandungCenter, 12);

            // OpenStreetMap Standard Tiles
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Command Center Pin
            const cmdIcon = L.divIcon({
                className: 'custom-cmd-marker',
                html: '<div style="background-color: #2563EB; width: 14px; height: 14px; border-radius: 9999px; border: 3px solid #ffffff; box-shadow: 0 0 12px #2563EB;"></div>',
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            L.marker(bandungCenter, { icon: cmdIcon })
                .addTo(map)
                .bindPopup('<strong style="font-size: 12px; color: #0A2240;">Komando DSDABM Kota Bandung</strong><br><span style="font-size: 11px; color: #64748B;">Jl. Cianjur No. 34, Kota Bandung</span>');

            // Sample Civic Telemetry Points
            const points = [
                { lat: -6.9218, lng: 107.6070, label: "Jalan Asia Afrika", cat: "Jalan", col: "#2563EB" },
                { lat: -6.8905, lng: 107.6100, label: "Drainase Pasteur", cat: "Drainase", col: "#0284C7" },
                { lat: -6.9024, lng: 107.6186, label: "PJU Dago", cat: "PJU", col: "#F59E0B" },
                { lat: -6.9380, lng: 107.6600, label: "Jembatan Gedebage", cat: "Jembatan", col: "#4F46E5" }
            ];

            points.forEach(p => {
                const icon = L.divIcon({
                    className: 'telemetry-marker',
                    html: `<div style="background-color: ${p.col}; width: 10px; height: 10px; border-radius: 9999px; border: 2px solid #ffffff; box-shadow: 0 0 8px ${p.col};"></div>`,
                    iconSize: [10, 10],
                    iconAnchor: [5, 5]
                });
                L.marker([p.lat, p.lng], { icon: icon })
                    .addTo(map)
                    .bindPopup(`<strong style="font-size: 12px; color: #0f172a;">${p.label}</strong><br><span style="font-size: 10px; color: ${p.col}; font-weight: bold;">Kategori: ${p.cat}</span>`);
            });
        });
    </script>
    @endpush
</x-warga-layout>
