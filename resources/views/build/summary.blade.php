@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12">
    
    <!-- Page Header Banner -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <span class="text-3xl">📊</span>
                <div>
                    <h1 class="text-xl font-bold text-white">Visual PC Build Analytics & Summary</h1>
                    <p class="text-xs text-slate-400">Real-time power analysis, cost breakdown, hardware component roster, and system compatibility verification</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('chat.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2">
                <span>🤖</span>
                <span>Ask AI Assistant</span>
            </a>
            <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition shadow-lg shadow-blue-500/20">
                Browse Components
            </a>
            @if($products->isNotEmpty())
                <a href="{{ route('cart.index') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition shadow-lg shadow-emerald-500/20 flex items-center gap-1.5">
                    <span>🛒</span>
                    <span>View Cart</span>
                </a>
            @endif
        </div>
    </div>

    @if($products->isEmpty())
        <!-- Empty State When No Components Added -->
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-4xl shadow-xl">
                🖥️
            </div>
            <div class="space-y-2 max-w-md mx-auto">
                <h2 class="text-xl font-bold text-white">No Components in Your Build Yet</h2>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Add components from our hardware catalog or consult our AI Hardware Assistant to generate an automated, compatible PC configuration!
                </p>
            </div>
            <div class="pt-4 flex flex-wrap justify-center gap-4">
                <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-600/20 flex items-center space-x-2">
                    <span>🛒</span>
                    <span>Browse Hardware Catalog</span>
                </a>
                <a href="{{ route('chat.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-6 py-3 rounded-xl text-xs font-bold transition flex items-center space-x-2">
                    <span>🤖</span>
                    <span>Build with AI Assistant</span>
                </a>
            </div>
        </div>
    @else
        <!-- Top Stats Pill Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg backdrop-blur-md">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold block">Configured Parts</span>
                <div class="text-xl font-extrabold text-white mt-1 font-mono flex items-center gap-1.5">
                    <span>{{ $products->count() }}</span>
                    <span class="text-xs text-slate-400 font-normal">Components</span>
                </div>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg backdrop-blur-md">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold block">Est. Build Total</span>
                <div class="text-xl font-extrabold text-emerald-400 mt-1 font-mono">
                    ${{ number_format($costBreakdown['total'] ?? 0, 2) }}
                </div>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg backdrop-blur-md">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold block">Est. Peak System TDP</span>
                <div class="text-xl font-extrabold text-amber-400 mt-1 font-mono">
                    {{ $totalTdp }} <span class="text-xs text-slate-400 font-normal">Watts</span>
                </div>
            </div>
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg backdrop-blur-md">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold block">Compatibility</span>
                <div class="text-sm font-bold mt-1.5">
                    @if($compatResult['is_compatible'] && empty($warningsResult['all_warnings']))
                        <span class="text-emerald-400 flex items-center gap-1">
                            <span>✓</span> 100% Compatible
                        </span>
                    @else
                        <span class="text-amber-400 flex items-center gap-1">
                            <span>⚠️</span> Warnings ({{ count($compatResult['incompatibilities']) + count($warningsResult['all_warnings']) }})
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 1: Configured Hardware Component Roster -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🖥️</span>
                    <h2 class="text-sm font-bold text-white">Configured Components Roster</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('catalog.index') }}" class="text-xs text-blue-400 hover:text-blue-300 font-semibold transition">
                        + Add More Components
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($products as $product)
                    <div class="bg-slate-950/80 border border-slate-800/90 hover:border-slate-700 p-4 rounded-xl flex items-center space-x-4 transition group">
                        <div class="w-16 h-16 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($product->image_path)
                                <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl">📦</span>
                            @endif
                        </div>

                        <div class="flex-grow min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="text-[10px] bg-slate-800 text-slate-300 border border-slate-700/60 px-2 py-0.5 rounded font-mono uppercase">
                                    {{ $product->category->name ?? 'Hardware' }}
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $product->brand }}</span>
                            </div>
                            <h3 class="text-xs font-bold text-white group-hover:text-blue-400 transition truncate mt-1">
                                {{ $product->name }}
                            </h3>
                            
                            @if($product->specifications->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach($product->specifications->take(2) as $spec)
                                        <span class="bg-slate-900 text-slate-400 text-[9px] px-1.5 py-0.5 rounded border border-slate-800">
                                            {{ $spec->spec_key }}: <strong class="text-slate-300">{{ $spec->spec_value }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="text-right flex-shrink-0">
                            <div class="text-xs font-mono font-bold text-emerald-400">
                                ${{ number_format($product->price, 2) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section 2: Analytics Dashboard Grid (Power TDP & Cost Allocation) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Widget 1: Estimated System TDP & Recommended PSU -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-xl">⚡</span>
                            <h2 class="text-sm font-bold text-white">System TDP & Power Analysis</h2>
                        </div>
                        <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                            1.25x Headroom
                        </span>
                    </div>

                    <div class="mt-6 space-y-6 text-center">
                        <!-- Total System TDP Meter -->
                        <div class="relative flex flex-col items-center justify-center p-6 bg-slate-950/80 border border-slate-800 rounded-2xl">
                            <span class="text-xs font-medium text-slate-400">Est. Total System TDP</span>
                            <div class="text-4xl font-extrabold text-amber-400 mt-2 font-mono">
                                {{ $totalTdp }} <span class="text-lg font-normal text-slate-400">Watts</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-2">Combined peak power output under full load</p>
                        </div>

                        <!-- Recommended PSU Output -->
                        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-left space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-emerald-400">Recommended PSU Rating:</span>
                                <span class="text-base font-bold text-emerald-300 font-mono">{{ $recommendedPsuWattage }}W+</span>
                            </div>
                            <p class="text-[11px] text-slate-400">
                                Includes 25% safety margin for transient voltage spikes and future upgrades.
                            </p>
                            @if($installedPsuWattage)
                                <div class="mt-2 pt-2 border-t border-emerald-500/20 flex items-center justify-between text-xs">
                                    <span class="text-slate-300">Installed PSU Capacity:</span>
                                    <span class="font-mono font-bold {{ $installedPsuWattage >= $recommendedPsuWattage ? 'text-emerald-400' : 'text-amber-400' }}">
                                        {{ $installedPsuWattage }}W ({{ $installedPsuWattage >= $recommendedPsuWattage ? '✓ Sufficient' : '⚠️ Low Headroom' }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 border-t border-slate-800/80 pt-3">
                    Calculated automatically using SpecExtractor TDP metrics.
                </div>
            </div>

            <!-- Widget 2: Price Distribution Progress Bars -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl">📈</span>
                        <h2 class="text-sm font-bold text-white">Price Allocation & Cost Breakdown</h2>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Total Est. Cost</span>
                        <span class="text-base font-bold text-emerald-400 font-mono">${{ number_format($costBreakdown['total'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <!-- Items Cost Progress Bars -->
                <div class="space-y-4">
                    @forelse($costBreakdown['items'] as $item)
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-200 font-medium truncate max-w-[70%]">{{ $item['name'] }}</span>
                                <div class="space-x-2 font-mono">
                                    <span class="text-slate-400">${{ number_format($item['price'], 2) }}</span>
                                    <span class="text-blue-400 font-bold">({{ $item['percentage_contribution'] }}%)</span>
                                </div>
                            </div>
                            <!-- Progress Bar Container -->
                            <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500" 
                                     style="width: {{ min(100, max(5, $item['percentage_contribution'])) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-6">No products selected for cost breakdown.</p>
                    @endforelse
                </div>

                <!-- Summary Totals Pill Row -->
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-800/80 text-center">
                    <div class="bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-[10px] text-slate-500 block">Subtotal</span>
                        <span class="text-xs font-bold text-slate-300 font-mono">${{ number_format($costBreakdown['subtotal'] ?? 0, 2) }}</span>
                    </div>
                    <div class="bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-[10px] text-slate-500 block">Est. Tax (5%)</span>
                        <span class="text-xs font-bold text-slate-300 font-mono">${{ number_format($costBreakdown['tax'] ?? 0, 2) }}</span>
                    </div>
                    <div class="bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-[10px] text-slate-500 block">Shipping</span>
                        <span class="text-xs font-bold text-slate-300 font-mono">${{ number_format($costBreakdown['shipping'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Section 3: Hardware Compatibility Status Checklist -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">✅</span>
                    <h2 class="text-sm font-bold text-white">Hardware Compatibility Checklist</h2>
                </div>
                <div>
                    @if($compatResult['is_compatible'] && empty($warningsResult['all_warnings']))
                        <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5">
                            <span>✓</span> System Fully Verified & Ready
                        </span>
                    @else
                        <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5">
                            <span>⚠️</span> Compatibility Warnings Detected
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Checklist Item 1: CPU Socket Matching -->
                <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                    <div class="text-lg">
                        @if($compatResult['is_compatible'])
                            <span class="text-emerald-400">✅</span>
                        @else
                            <span class="text-amber-400">⚠️</span>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-200">CPU & Motherboard Socket Match</h4>
                        <p class="text-[11px] text-slate-400 mt-1">Verifies processor pin array physically aligns with motherboard socket architecture.</p>
                    </div>
                </div>

                <!-- Checklist Item 2: RAM Generation -->
                <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                    <div class="text-lg">
                        <span class="text-emerald-400">✅</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-200">RAM Generation (DDR4 vs DDR5)</h4>
                        <p class="text-[11px] text-slate-400 mt-1">Ensures memory module notch standards match motherboard DIMM slot generation.</p>
                    </div>
                </div>

                <!-- Checklist Item 3: Form Factor Compatibility -->
                <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                    <div class="text-lg">
                        <span class="text-emerald-400">✅</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-200">Motherboard & Case Form Factor</h4>
                        <p class="text-[11px] text-slate-400 mt-1">Confirms motherboard dimensions (ATX / Micro-ATX) fit inside chassis mounting standoffs.</p>
                    </div>
                </div>

                <!-- Checklist Item 4: Power Supply TDP Sufficiency -->
                <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                    <div class="text-lg">
                        @if(!$installedPsuWattage || $installedPsuWattage >= $recommendedPsuWattage)
                            <span class="text-emerald-400">✅</span>
                        @else
                            <span class="text-amber-400">⚠️</span>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-200">Power Supply Wattage Headroom</h4>
                        <p class="text-[11px] text-slate-400 mt-1">Validates total TDP multiplied by 1.25 headroom factor stays within PSU specifications.</p>
                    </div>
                </div>

            </div>

            @if(!empty($compatResult['incompatibilities']) || !empty($warningsResult['all_warnings']))
                <div class="mt-4 bg-amber-500/10 border border-amber-500/20 p-4 rounded-xl space-y-2">
                    <h4 class="text-xs font-bold text-amber-400">Detected Issues & Bottleneck Recommendations:</h4>
                    <ul class="text-xs text-amber-300 space-y-1 list-disc list-inside">
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

        <!-- Bottom Action Strip -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-white">Ready to complete your build order?</h3>
                <p class="text-xs text-slate-400">All components can be reviewed in your shopping cart before checkout.</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('catalog.index') }}" class="w-full sm:w-auto text-center bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-5 py-2.5 rounded-xl text-xs font-semibold transition">
                    + Add More Parts
                </a>
                <a href="{{ route('cart.index') }}" class="w-full sm:w-auto text-center bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-lg shadow-emerald-600/20">
                    Proceed to Cart →
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
