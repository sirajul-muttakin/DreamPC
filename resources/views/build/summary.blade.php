@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12 animate-fade-in">
    
    <!-- Page Header -->
    <div class="bg-white/80 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-500/25">
                    📊
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white font-display tracking-tight">Visual PC Build Analytics & Summary</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Real-time system power analysis, cost breakdown, and automated hardware compatibility</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('chat.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <span>🤖</span>
                <span>Ask AI Assistant</span>
            </a>
            <a href="{{ route('catalog.index') }}" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2.5 rounded-2xl text-xs font-bold transition shadow-lg shadow-blue-500/20 text-center">
                Browse More Parts
            </a>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="bg-white/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-12 text-center space-y-4 shadow-xl">
            <span class="text-5xl opacity-40">🖥️</span>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white font-display">No PC Build Selected</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Add components from the catalog or request a full 7-part system from our AI Hardware Assistant to see live analytics!</p>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-500/20">
                    Explore Catalog
                </a>
                <a href="{{ route('chat.index') }}" class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-xl text-xs font-bold transition">
                    Ask AI Assistant
                </a>
            </div>
        </div>
    @else

        <!-- Configured Hardware Roster -->
        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6 backdrop-blur-xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🛠️</span>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white font-display">Configured Hardware Roster</h2>
                </div>
                <span class="text-xs font-mono font-bold text-slate-500">{{ $products->count() }} Components Selected</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($products as $product)
                    <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 p-4 rounded-2xl flex flex-col justify-between space-y-3 hover:border-blue-500/40 transition">
                        <div class="flex items-start space-x-3">
                            <div class="w-14 h-14 rounded-xl bg-slate-200 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden flex items-center justify-center flex-shrink-0">
                                @if(!empty($product->image_path))
                                    <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xl">🖥️</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-grow">
                                <span class="text-[9px] bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded-full font-mono uppercase font-bold">
                                    {{ $product->category->name ?? 'Component' }}
                                </span>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate mt-1.5">{{ $product->name }}</h4>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $product->brand }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($product->price, 2) }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">In Stock</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Analytics Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Widget 1: System TDP & Power Meter -->
            <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xl space-y-6 flex flex-col justify-between backdrop-blur-xl">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-xl">⚡</span>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white font-display">System TDP & Power Meter</h2>
                        </div>
                        <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider font-mono">
                            1.25x Headroom
                        </span>
                    </div>

                    <div class="mt-6 space-y-6 text-center">
                        <div class="relative flex flex-col items-center justify-center p-6 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Est. System TDP</span>
                            <div class="text-4xl font-black text-amber-500 mt-2 font-mono">
                                {{ $totalTdp }} <span class="text-sm font-bold text-slate-400">Watts</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2">Combined peak power draw under maximum load</p>
                        </div>

                        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-left space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Recommended PSU Output:</span>
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-300 font-mono">{{ $recommendedPsuWattage }}W+</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                Includes 25% safety overhead to handle transient voltage spikes and GPU overclocking.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-3 font-mono">
                    Calculated automatically using SpecExtractor TDP metrics.
                </div>
            </div>

            <!-- Widget 2: Price Distribution Progress Bars -->
            <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xl space-y-6 lg:col-span-2 backdrop-blur-xl">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl">📈</span>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white font-display">Price Allocation & Cost Breakdown</h2>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Total Estimated Cost</span>
                        <span class="text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($costBreakdown['total'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <!-- Items Cost Progress Bars -->
                <div class="space-y-4">
                    @forelse($costBreakdown['items'] as $item)
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-800 dark:text-slate-200 font-bold truncate max-w-[70%]">{{ $item['name'] }}</span>
                                <div class="space-x-2 font-mono">
                                    <span class="text-slate-500 font-semibold">${{ number_format($item['price'], 2) }}</span>
                                    <span class="text-blue-600 dark:text-blue-400 font-bold">({{ $item['percentage_contribution'] }}%)</span>
                                </div>
                            </div>
                            <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-950 rounded-full overflow-hidden border border-slate-200 dark:border-slate-800">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500" 
                                     style="width: {{ min(100, max(5, $item['percentage_contribution'])) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-6">No products selected for cost breakdown.</p>
                    @endforelse
                </div>

                <!-- Summary Totals Pill Row -->
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
                    <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Subtotal</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">${{ number_format($costBreakdown['subtotal'] ?? 0, 2) }}</span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Est. Tax (5%)</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">${{ number_format($costBreakdown['tax'] ?? 0, 2) }}</span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950/60 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider block">Shipping</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">${{ number_format($costBreakdown['shipping'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Widget 3: Automated Compatibility Checklist -->
        <div class="bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6 backdrop-blur-xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">✅</span>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white font-display">Automated Compatibility Checklist</h2>
                </div>
                <div>
                    @if($compatResult['is_compatible'] && empty($warningsResult['all_warnings']))
                        <span class="bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5 font-mono">
                            <span>✓</span> System 100% Fully Verified
                        </span>
                    @else
                        <span class="bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5 font-mono">
                            <span>⚠️</span> Compatibility Warnings Detected
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-start space-x-3">
                    <div class="text-lg">
                        @if($compatResult['is_compatible'])
                            <span class="text-emerald-500">✅</span>
                        @else
                            <span class="text-amber-500">⚠️</span>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">CPU & Motherboard Socket Match</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Verifies processor pin array aligns with motherboard socket architecture.</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-start space-x-3">
                    <div class="text-lg"><span class="text-emerald-500">✅</span></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">RAM Generation (DDR4 vs DDR5)</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Ensures memory module notch standards match motherboard DIMM slots.</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-start space-x-3">
                    <div class="text-lg"><span class="text-emerald-500">✅</span></div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Motherboard & Case Form Factor</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Confirms motherboard dimensions fit inside chassis mounting standoffs.</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 p-4 rounded-2xl flex items-start space-x-3">
                    <div class="text-lg">
                        @if($recommendedPsuWattage <= 850)
                            <span class="text-emerald-500">✅</span>
                        @else
                            <span class="text-amber-500">⚠️</span>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Power Supply Wattage Headroom</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Validates total TDP multiplied by 1.25 safety factor stays within PSU capacity.</p>
                    </div>
                </div>
            </div>

            @if(!empty($compatResult['incompatibilities']) || !empty($warningsResult['all_warnings']))
                <div class="mt-4 bg-amber-500/10 border border-amber-500/30 p-4 rounded-2xl space-y-2">
                    <h4 class="text-xs font-bold text-amber-700 dark:text-amber-400">Detected Issues & Bottleneck Recommendations:</h4>
                    <ul class="text-xs text-amber-800 dark:text-amber-300 space-y-1 list-disc list-inside">
                        @foreach($compatResult['incompatibilities'] as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                        @foreach($warningsResult['all_warnings'] as $warning)
                            <li><strong>{{ $warning['title'] }}:</strong> {{ $warning['message'] }} ({{ $warning['recommendation'] }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

    @endif
</div>
@endsection
